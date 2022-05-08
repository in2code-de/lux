<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\AttributeRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\Provider\AllowedMail;
use In2code\Lux\Domain\Service\VisitorMergeService;
use In2code\Lux\Events\AttributeCreateEvent;
use In2code\Lux\Events\AttributeOverwriteEvent;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\ObjectUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class AttributeTracker to add an attribute key/value pair to a visitor
 */
class AttributeTracker
{
    const CONTEXT_FIELDLISTENING = 'Fieldlistening';
    const CONTEXT_FORMLISTENING = 'Formlistening';
    const CONTEXT_EMAIL4LINK = 'Email4link';
    const CONTEXT_LUXLETTERLINK = 'Luxletterlink';
    const CONTEXT_FRONTENDUSER = 'Frontendauthentication';
    const CONTEXT_WORKFLOW = 'Workflow';

    /**
     * @var Visitor|null
     */
    protected $visitor = null;

    /**
     * Set different context for logging (attribute came from fieldlistening or from email4link and so on)
     *
     * @var string
     */
    protected $context = '';

    /**
     * @var int
     */
    protected $pageIdentifier = 0;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * @var AttributeRepository|null
     */
    protected $attributeRepository = null;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AttributeTracker constructor.
     *
     * @param Visitor $visitor
     * @param string $context
     * @param int $pageIdentifier
     */
    public function __construct(
        Visitor $visitor,
        string $context = self::CONTEXT_FIELDLISTENING,
        int $pageIdentifier = 0
    ) {
        $this->visitor = $visitor;
        $this->context = $context;
        $this->pageIdentifier = $pageIdentifier;
        $this->visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $this->attributeRepository = GeneralUtility::makeInstance(AttributeRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @param array $properties
     * @param array $allowedProperties
     * @return void
     * @throws EmailValidationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws DBALException
     * @throws InvalidConfigurationTypeException
     */
    public function addAttributes(array $properties, array $allowedProperties = [])
    {
        if ($allowedProperties !== []) {
            foreach (array_keys($properties) as $key) {
                if (in_array($key, $allowedProperties) === false) {
                    throw new ConfigurationException('Not allowed key given', 1619458671);
                }
            }
        }

        foreach ($properties as $key => $value) {
            $this->addAttribute($key, $value);
        }
    }

    /**
     * Add or update an attribute of a visitor and return the visitor
     *
     * @param string $key
     * @param string $value
     * @return void
     * @throws EmailValidationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     * @throws DBALException
     * @throws InvalidConfigurationTypeException
     */
    public function addAttribute(string $key, string $value)
    {
        $this->checkDisallowedMailProviders($key, $value);
        if ($this->isAttributeAddingEnabled($value)) {
            $attribute = $this->getAndUpdateAttributeFromDatabase($key, $value);
            if ($attribute === null) {
                $attribute = $this->createNewAttribute($key, $value);
                $this->visitor->addAttribute($attribute);
                $this->eventDispatcher->dispatch(
                    GeneralUtility::makeInstance(AttributeCreateEvent::class, $this->visitor, $attribute)
                );
            }
            if ($attribute->isEmail()) {
                if ($this->visitor->isIdentified() === false) {
                    $className = 'In2code\Lux\Events\Log\LogVisitorIdentifiedBy' . $this->context . 'Event';
                    $this->eventDispatcher->dispatch(
                        GeneralUtility::makeInstance(
                            $className,
                            $this->visitor,
                            $attribute,
                            $this->pageIdentifier
                        )
                    );
                }
                $this->visitor->setIdentified(true);
                $this->visitor->setEmail($value);
                $this->visitor->setFrontenduserAutomatically();
            }
            $this->visitorRepository->update($this->visitor);
            $this->visitorRepository->persistAll();

            $this->mergeVisitorsOnGivenEmail($key, $value);
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     * @throws EmailValidationException
     * @throws InvalidConfigurationTypeException
     */
    protected function checkDisallowedMailProviders(string $key, string $value)
    {
        if ($key === 'email') {
            $mailProviderService = GeneralUtility::makeInstance(AllowedMail::class);
            if ($mailProviderService->isEmailAllowed($value) === false) {
                throw new EmailValidationException('Email is not allowed', 1555427969);
            }
        }
    }

    /**
     * Get fitting attribute to a given key. If found: just update and return. If not found, return null.
     *
     * @param string $key
     * @param string $value
     * @return Attribute|object|null
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function getAndUpdateAttributeFromDatabase(string $key, string $value)
    {
        $attribute = $this->attributeRepository->findByVisitorAndKey($this->visitor, $key);
        if ($attribute !== null) {
            $attribute->setValue($value);
            $this->attributeRepository->update($attribute);
            $this->eventDispatcher->dispatch(
                GeneralUtility::makeInstance(AttributeOverwriteEvent::class, $this->visitor, $attribute)
            );
        }
        return $attribute;
    }

    /**
     * @param string $key
     * @param string $value
     * @return Attribute
     */
    protected function createNewAttribute(string $key, string $value): Attribute
    {
        $attribute = GeneralUtility::makeInstance(Attribute::class);
        $attribute->setName($key);
        $attribute->setValue($value);
        return $attribute;
    }

    /**
     * If email is given and there is already an email stored but with a different fingerprint, merge everything to the
     * oldest visitor
     *
     * @param string $key
     * @param string $value
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     */
    protected function mergeVisitorsOnGivenEmail(string $key, string $value)
    {
        if ($key === Attribute::KEY_NAME) {
            $mergeService = GeneralUtility::makeInstance(VisitorMergeService::class);
            $mergeService->mergeByEmail($value);
        }
    }

    /**
     * @param string $value
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isAttributeAddingEnabled(string $value): bool
    {
        return !empty($value) && $this->visitor->isNotBlacklisted() && $this->isEnabledIdentificationInSettings();
    }

    /**
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isEnabledIdentificationInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['identification']['_enable']) && $settings['identification']['_enable'] === '1';
    }
}

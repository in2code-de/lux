<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\AttributeRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\AllowedMailProvidersService;
use In2code\Lux\Domain\Service\VisitorMergeService;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class AttributeTracker to add an attribute key/value pair to a visitor
 */
class AttributeTracker
{
    use SignalTrait;

    const CONTEXT_FIELDLISTENING = 'Fieldlistening';
    const CONTEXT_FORMLISTENING = 'Formlistening';
    const CONTEXT_EMAIL4LINK = 'Email4link';
    const CONTEXT_LUXLETTERLINK = 'Luxletterlink';
    const CONTEXT_FRONTENDUSER = 'Frontendauthentication';

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
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * @var AttributeRepository|null
     */
    protected $attributeRepository = null;

    /**
     * AttributeTracker constructor.
     *
     * @param Visitor $visitor
     * @param string $context
     */
    public function __construct(Visitor $visitor, string $context = self::CONTEXT_FIELDLISTENING)
    {
        $this->visitor = $visitor;
        $this->context = $context;
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $this->attributeRepository = ObjectUtility::getObjectManager()->get(AttributeRepository::class);
    }

    /**
     * @param array $properties
     * @return void
     * @throws DBALException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    public function addAttributes(array $properties)
    {
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
     * @throws DBALException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function addAttribute(string $key, string $value)
    {
        $this->checkDisallowedMailProviders($key, $value);
        if ($this->isAttributeAddingEnabled($value)) {
            $attribute = $this->getAndUpdateAttributeFromDatabase($key, $value);
            if ($attribute === null) {
                $attribute = $this->createNewAttribute($key, $value);
                $this->visitor->addAttribute($attribute);
                $this->signalDispatch(__CLASS__, 'createNewAttribute', [$attribute, $this->visitor]);
            }
            if ($attribute->isEmail()) {
                if ($this->visitor->isIdentified() === false) {
                    $this->signalDispatch(__CLASS__, 'isIdentifiedBy' . $this->context, [$attribute, $this->visitor]);
                }
                $this->visitor->setIdentified(true);
                $this->visitor->setEmail($value);
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
     */
    protected function checkDisallowedMailProviders(string $key, string $value)
    {
        if ($key === 'email') {
            $mailProviderService = ObjectUtility::getObjectManager()->get(AllowedMailProvidersService::class);
            if ($mailProviderService->isEmailAllowed($value) === false) {
                throw new \LogicException('Email is not allowed', 1555427969);
            }
        }
    }

    /**
     * Get fitting attribute to a given key. If found: just update and return. If not found, return null.
     *
     * @param string $key
     * @param string $value
     * @return Attribute|null|object
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    protected function getAndUpdateAttributeFromDatabase(string $key, string $value)
    {
        $attribute = $this->attributeRepository->findByVisitorAndKey($this->visitor, $key);
        if ($attribute !== null) {
            $attribute->setValue($value);
            $this->attributeRepository->update($attribute);
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$attribute]);
        return $attribute;
    }

    /**
     * @param string $key
     * @param string $value
     * @return Attribute
     */
    protected function createNewAttribute(string $key, string $value): Attribute
    {
        /** @var Attribute $attribute */
        $attribute = ObjectUtility::getObjectManager()->get(Attribute::class);
        $attribute->setName($key);
        $attribute->setValue($value);
        return $attribute;
    }

    /**
     * If email is given and there is already an email stored but with a different idCookie, merge everything to the
     * oldest visitor
     *
     * @param string $key
     * @param string $value
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     */
    protected function mergeVisitorsOnGivenEmail(string $key, string $value)
    {
        if ($key === Attribute::KEY_NAME) {
            $mergeService = ObjectUtility::getObjectManager()->get(VisitorMergeService::class, $value);
            $mergeService->merge();
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function isAttributeAddingEnabled(string $value): bool
    {
        return !empty($value) && $this->visitor->isNotBlacklisted() && $this->isEnabledIdentificationInSettings();
    }

    /**
     * @return bool
     */
    protected function isEnabledIdentificationInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['identification']['_enable']) && $settings['identification']['_enable'] === '1';
    }
}

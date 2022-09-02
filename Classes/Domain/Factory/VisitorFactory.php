<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Factory\Ipinformation\Handler;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\VisitorMergeService;
use In2code\Lux\Events\Log\LogVisitorEvent;
use In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent;
use In2code\Lux\Events\VisitorFactoryAfterCreateNewEvent;
use In2code\Lux\Events\VisitorFactoryBeforeCreateNewEvent;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\FileNotFoundException;
use In2code\Lux\Exception\FingerprintMustNotBeEmptyException;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\CookieUtility;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\StringUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class VisitorFactory to add a new visitor to database (if not yet stored).
 */
class VisitorFactory
{
    /**
     * @var Fingerprint
     */
    protected $fingerprint = null;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * VisitorFactory constructor.
     *
     * @param string $identificator
     * @param bool $tempVisitor If there is no fingerprint (doNotTrack) but we even want to generate a visitor object
     * @throws FingerprintMustNotBeEmptyException
     */
    public function __construct(string $identificator, bool $tempVisitor = false)
    {
        if ($identificator === '' && $tempVisitor === true) {
            $identificator = StringUtility::getRandomString(32, false);
        }
        $this->fingerprint = GeneralUtility::makeInstance(Fingerprint::class)->setValue($identificator);
        $this->visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(StopAnyProcessBeforePersistenceEvent::class, $this->fingerprint)
        );
    }

    /**
     * @return Visitor
     * @throws ConfigurationException
     * @throws DBALException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws FileNotFoundException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
     */
    public function getVisitor(): Visitor
    {
        $visitor = $this->getVisitorFromDatabaseByFingerprint();
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(VisitorFactoryBeforeCreateNewEvent::class, $visitor, $this->fingerprint)
        );
        if ($visitor === null) {
            $visitor = $this->createNewVisitor();
            $this->visitorRepository->add($visitor);
            $this->visitorRepository->persistAll();
        }
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(VisitorFactoryAfterCreateNewEvent::class, $visitor, $this->fingerprint)
        );
        return $visitor;
    }

    /**
     * Check if there is a visitor already stored in database by given fingerprint. Also legacy luxId-cookie will be
     * respected, to not loose visitors when changing lux from 6.x to 7.x
     *
     * @return Visitor|null
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     * @throws DBALException
     */
    protected function getVisitorFromDatabaseByFingerprint(): ?Visitor
    {
        $visitor = $this->visitorRepository->findOneAndAlsoBlacklistedByFingerprint(
            $this->fingerprint->getValue(),
            $this->fingerprint->getType()
        );
        if ($visitor === null && CookieUtility::getLuxId() !== '') {
            $visitor = $this->getVisitorFromDatabaseByLegacyCookie();
        }
        $mergeService = GeneralUtility::makeInstance(VisitorMergeService::class);
        $mergeService->mergeByFingerprint($this->fingerprint->getValue());
        return $visitor;
    }

    /**
     * @return Visitor|null
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function getVisitorFromDatabaseByLegacyCookie(): ?Visitor
    {
        $visitor = $this->visitorRepository->findOneAndAlsoBlacklistedByFingerprint(
            CookieUtility::getLuxId(),
            Fingerprint::TYPE_COOKIE
        );
        if ($visitor !== null) {
            $visitor->addFingerprint($this->fingerprint);
            $this->visitorRepository->update($visitor);
            $this->visitorRepository->persistAll();
        }
        return $visitor;
    }

    /**
     * @return Visitor
     * @throws ConfigurationException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws FileNotFoundException
     * @throws InvalidConfigurationTypeException
     */
    protected function createNewVisitor(): Visitor
    {
        $visitor = GeneralUtility::makeInstance(Visitor::class);
        $visitor->addFingerprint($this->fingerprint);
        $this->enrichNewVisitorWithIpInformation($visitor);
        $visitor->setCompanyAutomatic(); // must be after enrichNewVisitorWithIpInformation()
        /** @var LogVisitorEvent $event */
        $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(LogVisitorEvent::class, $visitor));
        return $event->getVisitor();
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws ConfigurationException
     */
    protected function enrichNewVisitorWithIpInformation(Visitor $visitor)
    {
        if (ConfigurationUtility::isIpLoggingDisabled() === false) {
            $handler = GeneralUtility::makeInstance(Handler::class);
            $visitor->setIpinformations($handler->getObjectStorage());
            $visitor->setIpAddress($this->getIpAddress());
        }
    }

    /**
     * Decide if the IP-address must be anonymized or not
     *
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getIpAddress(): string
    {
        $ipAddress = IpUtility::getIpAddress();
        if (ConfigurationUtility::isAnonymizeIpEnabled()) {
            $parts = explode('.', $ipAddress);
            $keys = array_keys($parts);
            $parts[end($keys)] = '***';
            $ipAddress = implode('.', $parts);
        }
        return $ipAddress;
    }
}

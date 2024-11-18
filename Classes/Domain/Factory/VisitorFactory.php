<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Factory\Ipinformation\Handler;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Domain\Service\VisitorMergeService;
use In2code\Lux\Events\Log\LogVisitorEvent;
use In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent;
use In2code\Lux\Events\VisitorFactoryAfterCreateNewEvent;
use In2code\Lux\Events\VisitorFactoryBeforeCreateNewEvent;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\FingerprintMustNotBeEmptyException;
use In2code\Lux\Exception\Validation\IdentificatorFormatException;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\CookieUtility;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\StringUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class VisitorFactory to add a new visitor to database (if not yet stored).
 */
class VisitorFactory
{
    protected ?Fingerprint $fingerprint = null;
    protected ?VisitorRepository $visitorRepository = null;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param string $identificator
     * @param bool $tempVisitor If there is no fingerprint (doNotTrack) but we even want to generate a visitor object
     * @throws FingerprintMustNotBeEmptyException
     * @throws IdentificatorFormatException
     */
    public function __construct(string $identificator, bool $tempVisitor = false)
    {
        $this->checkIdentificator($identificator);
        if ($identificator === '' && $tempVisitor === true) {
            $identificator = StringUtility::getRandomString(Fingerprint::IDENTIFICATOR_LENGTH_FINGERPRINT, false);
        }
        $this->fingerprint = GeneralUtility::makeInstance(Fingerprint::class)
            ->setValue($identificator)
            ->setSite(GeneralUtility::makeInstance(SiteService::class)
            ->getSiteIdentifierFromPageIdentifier(FrontendUtility::getCurrentPageIdentifier()));
        $this->visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(StopAnyProcessBeforePersistenceEvent::class, $this->fingerprint)
        );
    }

    /**
     * @return Visitor
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
     * @throws ExceptionDbalDriver
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
     * respected, to not lose visitors when changing lux from 6.x to 7.x
     *
     * @return Visitor|null
     * @throws ConfigurationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     */
    protected function createNewVisitor(): Visitor
    {
        $visitor = GeneralUtility::makeInstance(Visitor::class)
            ->addFingerprint($this->fingerprint);
        $this->enrichNewVisitorWithIpInformation($visitor);
        $visitor->resetCompanyAutomatic(); // must be after enrichNewVisitorWithIpInformation()
        /** @var LogVisitorEvent $event */
        $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(LogVisitorEvent::class, $visitor));
        return $event->getVisitor();
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ConfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
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
            $ipAddress = IpUtility::getIpAddressAnonymized($ipAddress);
        }
        return $ipAddress;
    }

    protected function checkIdentificator(string $identificator): void
    {
        $length = [0, Fingerprint::IDENTIFICATOR_LENGTH_FINGERPRINT, Fingerprint::IDENTIFICATOR_LENGTH_STORAGE];
        if (in_array(strlen($identificator), $length) === false) {
            throw new IdentificatorFormatException('Identificator is in wrong format: ' . $identificator, 1680203759);
        }
        if (preg_replace('/[a-z0-9]{' . Fingerprint::IDENTIFICATOR_LENGTH_FINGERPRINT . ',' . Fingerprint::IDENTIFICATOR_LENGTH_STORAGE . '}/', '', $identificator) !== '') {
            throw new IdentificatorFormatException('Identificator is in wrong format: ' . $identificator, 1680204272);
        }
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\FingerprintMustNotBeEmptyException;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\CookieUtility;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class VisitorFactory to add a new visitor to database (if not yet stored).
 */
class VisitorFactory
{
    use SignalTrait;

    /**
     * @var Fingerprint
     */
    protected $fingerprint = null;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * VisitorFactory constructor.
     *
     * @param string $fingerprint
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     * @throws FingerprintMustNotBeEmptyException
     */
    public function __construct(string $fingerprint)
    {
        $this->fingerprint = GeneralUtility::makeInstance(Fingerprint::class)->setValue($fingerprint);
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $this->signalDispatch(__CLASS__, 'stopAnyProcessBeforePersistence', [$this->fingerprint]);
    }

    /**
     * @return Visitor
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     * @throws UnknownObjectException
     */
    public function getVisitor(): Visitor
    {
        $visitor = $this->getVisitorFromDatabaseByFingerprint();
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'beforeCreateNew', [$this->fingerprint]);
        if ($visitor === null) {
            $visitor = $this->createNewVisitor();
            $this->visitorRepository->add($visitor);
            $this->visitorRepository->persistAll();
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$this->fingerprint]);
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
     */
    protected function getVisitorFromDatabaseByFingerprint(): ?Visitor
    {
        $visitor = $this->visitorRepository->findOneAndAlsoBlacklistedByFingerprint($this->fingerprint->getValue());
        if ($visitor === null && CookieUtility::getLuxId() !== '') {
            $visitor = $this->getVisitorFromDatabaseByLegacyCookie();
        }
        return $visitor;
    }

    /**
     * @return Visitor|null
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     */
    protected function createNewVisitor(): Visitor
    {
        $visitor = GeneralUtility::makeInstance(Visitor::class);
        $visitor->addFingerprint($this->fingerprint);
        $this->enrichNewVisitorWithIpInformation($visitor);
        $this->signalDispatch(__CLASS__, 'newVisitor', [$visitor]);
        return $visitor;
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     */
    protected function enrichNewVisitorWithIpInformation(Visitor $visitor)
    {
        if (ConfigurationUtility::isIpLoggingDisabled() === false) {
            if (ConfigurationUtility::isIpInformationDisabled() === false) {
                $ipInformationFactory = ObjectUtility::getObjectManager()->get(IpinformationFactory::class);
                $objectStorage = $ipInformationFactory->getObjectStorageWithIpinformation();
                $visitor->setIpinformations($objectStorage);
            }
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
            $parts[end(array_keys($parts))] = '***';
            $ipAddress = implode('.', $parts);
        }
        return $ipAddress;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;

/**
 * Class VisitorFactory to add a new visitor to database (if not yet stored).
 */
class VisitorFactory
{
    use SignalTrait;

    /**
     * @var string
     */
    protected $idCookie = '';

    /**
     * @var string
     */
    protected $referrer = '';

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * VisitorFactory constructor.
     *
     * @param string $idCookie
     * @param string $referrer
     */
    public function __construct(string $idCookie, string $referrer = '')
    {
        $this->idCookie = $idCookie;
        $this->referrer = $referrer;
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
    }

    /**
     * @return Visitor
     * @throws IllegalObjectTypeException
     * @throws UnsupportedMethodException
     */
    public function getVisitor(): Visitor
    {
        $visitor = $this->getVisitorFromDatabase();
        if ($visitor === null) {
            $visitor = $this->createNewVisitor();
            $this->visitorRepository->add($visitor);
            $this->visitorRepository->persistAll();
        }
        return $visitor;
    }

    /**
     * @return Visitor|null
     * @throws UnsupportedMethodException
     */
    protected function getVisitorFromDatabase()
    {
        return $this->visitorRepository->findOneAndAlsoBlacklistedByIdCookie($this->idCookie);
    }

    /**
     * @return Visitor
     */
    protected function createNewVisitor(): Visitor
    {
        /** @var Visitor $visitor */
        $visitor = GeneralUtility::makeInstance(Visitor::class);
        $visitor->setIdCookie($this->idCookie);
        $visitor->setUserAgent(GeneralUtility::getIndpEnv('HTTP_USER_AGENT'));
        $visitor->setReferrer($this->referrer);
        $this->enrichNewVisitorWithIpInformation($visitor);
        $this->signalDispatch(__CLASS__, 'newVisitor', [$visitor]);
        return $visitor;
    }

    /**
     * @param Visitor $visitor
     * @return void
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

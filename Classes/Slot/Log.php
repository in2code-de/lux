<?php
declare(strict_types = 1);
namespace In2code\Lux\Slot;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\LogService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class Log
 */
class Log implements SingletonInterface
{
    /**
     * @var LogService|null
     */
    protected $logService = null;

    /**
     * @param LogService $logService
     * @return void
     * @noinspection PhpUnused
     */
    public function injectFormRepository(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logNewVisitor(Visitor $visitor)
    {
        $this->logService->logNewVisitor($visitor);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitor(Attribute $attribute, Visitor $visitor, int $pageIdentifier)
    {
        $this->logService->logIdentifiedVisitor($visitor, $pageIdentifier);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFormListening(Attribute $attribute, Visitor $visitor, int $pageIdentifier)
    {
        $this->logService->logIdentifiedVisitorFormListening($visitor, $pageIdentifier);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByEmail4Link(Attribute $attribute, Visitor $visitor, int $pageIdentifier)
    {
        $this->logService->logIdentifiedVisitorByEmail4Link($visitor, $pageIdentifier);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByLuxletterlink(Attribute $attribute, Visitor $visitor, int $pageIdentifier)
    {
        $this->logService->logIdentifiedVisitorByLuxletterlink($visitor, $pageIdentifier);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function logIdentifiedVisitorByFrontendauthentication(
        Attribute $attribute,
        Visitor $visitor,
        int $pageIdentifier
    ) {
        $this->logService->logIdentifiedVisitorByFrontendauthentication($visitor, $pageIdentifier);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws Exception
     */
    public function logEmail4LinkEmail(Visitor $visitor, string $href)
    {
        $this->logService->logEmail4LinkEmail($visitor, $href);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws Exception
     */
    public function logEmail4LinkEmailFailed(Visitor $visitor, string $href)
    {
        $this->logService->logEmail4LinkEmailFailed($visitor, $href);
    }

    /**
     * @param Download $download
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function logDownload(Download $download, Visitor $visitor)
    {
        $this->logService->logDownload($download);
    }

    /**
     * @param Visitor $visitor
     * @param int $searchIdentifier
     * @return void
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function logSearch(Visitor $visitor, int $searchIdentifier)
    {
        $this->logService->logSearch($visitor, $searchIdentifier);
    }

    /**
     * @param Visitor $visitor
     * @param Linklistener $linklistener
     * @param int $pageUid
     * @return void
     * @throws Exception
     */
    public function logLinkClick(Visitor $visitor, Linklistener $linklistener, int $pageUid)
    {
        $this->logService->logLinkListener($visitor, $linklistener, $pageUid);
    }
}

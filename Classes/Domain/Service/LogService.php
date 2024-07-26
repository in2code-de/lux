<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Luxenterprise\Domain\Model\AbTestingPage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class LogService
{
    /**
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logNewVisitor(Visitor $visitor): void
    {
        $this->log(Log::STATUS_NEW, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitor(Visitor $visitor, int $pageIdentifier): void
    {
        $this->log(Log::STATUS_IDENTIFIED, $visitor, ['pageUid' => $pageIdentifier]);
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorFormListening(Visitor $visitor, int $pageIdentifier): void
    {
        $this->log(Log::STATUS_IDENTIFIED_FORMLISTENING, $visitor, ['pageUid' => $pageIdentifier]);
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByEmail4Link(Visitor $visitor, int $pageIdentifier): void
    {
        $this->log(Log::STATUS_IDENTIFIED_EMAIL4LINK, $visitor, ['pageUid' => $pageIdentifier]);
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByLuxletterlink(Visitor $visitor, int $pageIdentifier): void
    {
        $this->log(Log::STATUS_IDENTIFIED_LUXLETTERLINK, $visitor, ['pageUid' => $pageIdentifier]);
    }

    /**
     * @param Visitor $visitor
     * @param int $pageIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFrontendauthentication(Visitor $visitor, int $pageIdentifier): void
    {
        $this->log(Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION, $visitor, ['pageUid' => $pageIdentifier]);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmail(Visitor $visitor, string $href): void
    {
        $this->log(Log::STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAIL, $visitor, ['href' => $href]);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmailFailed(Visitor $visitor, string $href): void
    {
        $this->log(Log::STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAILFAILED, $visitor, ['href' => $href]);
    }

    /**
     * @param Visitor $visitor
     * @param string $parameter
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logVirtualPageRequest(Visitor $visitor, string $parameter): void
    {
        $this->log(Log::STATUS_VIRTUALPAGEVISIT, $visitor, ['virtualPath' => $parameter]);
    }

    /**
     * @param Download $download
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logDownload(Download $download): void
    {
        $this->log(Log::STATUS_DOWNLOAD, $download->getVisitor(), ['href' => $download->getHref()]);
    }

    /**
     * @param Utm $utm
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logUtm(Utm $utm): void
    {
        $visitor = $utm->getVisitor();
        if ($visitor !== null) {
            $this->log(Log::STATUS_UTM_TRACK, $visitor, ['utm' => $utm->getUid()]);
        }
    }

    /**
     * @param Visitor $visitor
     * @param int $searchIdentifier
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logSearch(Visitor $visitor, int $searchIdentifier): void
    {
        $this->log(Log::STATUS_SEARCH, $visitor, ['search' => $searchIdentifier]);
    }

    /**
     * @param Visitor $visitor
     * @param Linklistener $linklistener
     * @param int $pageUid
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logLinkListener(Visitor $visitor, Linklistener $linklistener, int $pageUid): void
    {
        $this->log(
            Log::STATUS_LINKLISTENER,
            $visitor,
            ['linklistener' => $linklistener->getUid(), 'pageUid' => $pageUid]
        );
    }

    /**
     * @param QueryResultInterface $visitors
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logVisitorMergeByFingerprint(QueryResultInterface $visitors): void
    {
        $visitor = $visitors->getFirst();
        $identifiers = [];
        foreach ($visitors as $visitor) {
            $identifiers[] = $visitor->getUid();
        }
        $this->log(
            Log::STATUS_MERGE_BYFINGERPRINT,
            $visitor,
            ['visitorUidsMergedIntoFirst' => implode(',', $identifiers)]
        );
    }

    /**
     * @param QueryResultInterface $visitors
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logVisitorMergeByEmail(QueryResultInterface $visitors): void
    {
        $visitor = $visitors->getFirst();
        $identifiers = [];
        foreach ($visitors as $visitor) {
            $identifiers[] = $visitor->getUid();
        }
        $this->log(
            Log::STATUS_MERGE_BYEMAIL,
            $visitor,
            ['visitorUidsMergedIntoFirst' => implode(',', $identifiers)]
        );
    }

    /**
     * @param Visitor $visitor
     * @param int $shownContentUid
     * @param int $containerContentUid
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logContextualContent(Visitor $visitor, int $shownContentUid, int $containerContentUid): void
    {
        $this->log(
            Log::STATUS_CONTEXTUAL_CONTENT,
            $visitor,
            [
                'shownContentUid' => $shownContentUid,
                'containerContentUid' => $containerContentUid,
            ]
        );
    }

    /**
     * @param Visitor $visitor
     * @param AbTestingPage $abTestingPage
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logAbTestingPage(Visitor $visitor, AbTestingPage $abTestingPage): void
    {
        $this->log(
            Log::STATUS_ABTESTING_PAGE,
            $visitor,
            [
                'abTestingPage' => $abTestingPage->getUid(),
                'pageUid' => $abTestingPage->getPid(),
            ]
        );
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logWiredmindsConnection(Visitor $visitor): void
    {
        $this->log(Log::STATUS_WIREDMINDS_CONNECTION, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logWiredmindsConnectionSuccess(Visitor $visitor): void
    {
        $this->log(Log::STATUS_WIREDMINDS_SUCCESSFUL, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @param string $message exception message
     * @param int $code an exception timestamp
     * @param string $source path + file and line of error - like /var/www/file.php:123
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logError(Visitor $visitor, string $message, int $code, string $source): void
    {
        $this->log(
            Log::STATUS_ERROR,
            $visitor,
            [
                'message' => $message,
                'code' => $code,
                'source' => $source,
            ]
        );
    }

    /**
     * @param int $status
     * @param Visitor $visitor
     * @param array $properties
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function log(int $status, Visitor $visitor, array $properties = []): void
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        $siteIdentifier = $siteService->getSiteIdentifierFromPageIdentifier(FrontendUtility::getCurrentPageIdentifier());

        $log = GeneralUtility::makeInstance(Log::class)
            ->setStatus($status)
            ->setPropertiesArray($properties)
            ->setSite($siteIdentifier);
        $logRepository->add($log);
        $visitor->addLog($log);
        if ($visitor->getUid() > 0) {
            $visitorRepository->update($visitor);
        } else {
            $visitorRepository->add($visitor);
        }
        $logRepository->persistAll();
    }
}

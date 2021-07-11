<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class LogService
 */
class LogService
{
    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logNewVisitor(Visitor $visitor): void
    {
        $this->log(Log::STATUS_NEW, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitor(Visitor $visitor): void
    {
        $this->log(Log::STATUS_IDENTIFIED, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorFormListening(Visitor $visitor): void
    {
        $this->log(Log::STATUS_IDENTIFIED_FORMLISTENING, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByEmail4Link(Visitor $visitor): void
    {
        $this->log(Log::STATUS_IDENTIFIED_EMAIL4LINK, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByLuxletterlink(Visitor $visitor): void
    {
        $this->log(Log::STATUS_IDENTIFIED_LUXLETTERLINK, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFrontendauthentication(Visitor $visitor): void
    {
        $this->log(Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION, $visitor);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws Exception
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
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmailFailed(Visitor $visitor, string $href): void
    {
        $this->log(Log::STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAILFAILED, $visitor, ['href' => $href]);
    }

    /**
     * @param Download $download
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logDownload(Download $download): void
    {
        $this->log(Log::STATUS_DOWNLOAD, $download->getVisitor(), ['href' => $download->getHref()]);
    }

    /**
     * @param Visitor $visitor
     * @param int $searchIdentifier
     * @return void
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
                'containerContentUid' => $containerContentUid
            ]
        );
    }

    /**
     * @param Visitor $visitor
     * @param string $message exception message
     * @param int $code an exception timestamp
     * @param string $source path + file and line of error - like /var/www/file.php:123
     * @return void
     * @throws Exception
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
                'source' => $source
            ]
        );
    }

    /**
     * @param int $status
     * @param Visitor $visitor
     * @param array $properties
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function log(int $status, Visitor $visitor, array $properties = []): void
    {
        $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);

        $log = ObjectUtility::getObjectManager()->get(Log::class)->setStatus($status)->setProperties($properties);
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

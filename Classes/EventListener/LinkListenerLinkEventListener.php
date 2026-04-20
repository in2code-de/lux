<?php

declare(strict_types=1);
namespace In2code\Lux\EventListener;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Frontend\Event\AfterLinkIsGeneratedEvent;

class LinkListenerLinkEventListener
{
    /**
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public function __invoke(AfterLinkIsGeneratedEvent $event): void
    {
        $attributes = $event->getLinkResult()->getAttributes();
        if (isset($attributes['data-lux-linklistener'])) {
            $linkListenerUid = (int)$attributes['data-lux-linklistener'];
            $url = $this->getTargetUrl($linkListenerUid, $event);
            $event->setLinkResult($event->getLinkResult()->withAttribute('href', $url));
        }
    }

    /**
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    protected function getTargetUrl(int $linkListenerUid, AfterLinkIsGeneratedEvent $event): string
    {
        $configuration = [
            'parameter' => $this->getLinkTargetFromLinkListenerIdentifier($linkListenerUid),
        ];
        return $event->getContentObjectRenderer()->typoLink_URL($configuration);
    }

    /**
     * @throws ExceptionDbal
     */
    protected function getLinkTargetFromLinkListenerIdentifier(int $linkListenerUid): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linklistener::TABLE_NAME);
        return (string)$queryBuilder
            ->select('link')
            ->from(Linklistener::TABLE_NAME)
            ->where('uid=' . (int)$linkListenerUid)
            ->executeQuery()
            ->fetchOne();
    }
}

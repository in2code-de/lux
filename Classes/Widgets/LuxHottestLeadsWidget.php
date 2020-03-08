<?php
declare(strict_types=1);
namespace In2code\Lux\Widgets;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Dashboard\Widgets\AbstractListWidget;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxHottestLeadsWidget
 * @noinspection PhpUnused
 */
class LuxHottestLeadsWidget extends AbstractListWidget
{
    protected $title =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.hottestleads.title';
    protected $description =
        'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.hottestleads.description';
    protected $iconIdentifier = 'extension-lux-turquoise';
    protected $height = 4;
    protected $width = 2;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     * @throws RouteNotFoundException
     */
    public function initializeView(): void
    {
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISMONTH);
        $visitors = $visitorRepository->findByHottestScorings($filter);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $uriBuilder = ObjectUtility::getObjectManager()->get(UriBuilder::class);
            $uri = $uriBuilder->buildUriFromRoute(
                'lux_LuxLeads',
                [
                    'tx_lux_lux_luxleads' => [
                        'action' => 'detail',
                        'visitor' => $visitor->getUid()
                    ]
                ]
            );
            $this->items[] = [
                'title' => $visitor->getFullName() . ' (' . $visitor->getScoring() . ')',
                'link' => $uri,
                'pubDate' => $visitor->getTstamp()->format('r'),
                'description' => $visitor->getFullName() . ' (' . $visitor->getScoring() . ')',
            ];
        }

        parent::initializeView();
    }
}

<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Pageoverview\Analysis;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Hooks\PageOverview;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Title extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = PageOverview::class;
    protected string $cacheLayerFunction = 'render';

    protected function assignAdditionalVariables(): array
    {
        $session = BackendUtility::getSessionValue('toggle', 'pageOverview', 'General');
        $additionalVariables = [
            'pageIdentifier' => $this->getArgument('pageidentifier'),
            'status' => $session['status'] ?? 'show',
            'view' => ucfirst(ConfigurationUtility::getPageOverviewView()),
        ];

        if ($this->cacheLayer->isCacheAvailable('PageOverviewTitle' . $this->getArgument('pageidentifier'))) {
            return $additionalVariables;
        }

        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_LAST7DAYS)
            ->setSearchterm($this->getArgument('pageidentifier'));
        $delta = $pagevisitRepository->compareAmountPerPage(
            (int)$this->getArgument('pageidentifier'),
            $filter,
            ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
        );

        $additionalVariables = array_merge($additionalVariables, [
            'abandons' => $pagevisitRepository->findAbandonsForPage((int)$this->getArgument('pageidentifier'), $filter),
            'delta' => $delta,
            'deltaIconPath' => $delta >= 0 ? 'Icons/increase.svg' : 'Icons/decrease.svg',
            'visits' => $pagevisitRepository->findAmountPerPage((int)$this->getArgument('pageidentifier'), $filter),
        ]);

        return $additionalVariables;
    }
}

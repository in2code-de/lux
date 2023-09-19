<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Analysis\Dashboard;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Pagevisits extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = AnalysisController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Analysis';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        if ($this->cacheLayer->isCacheAvailable('Box/Analysis/Pagevisits/' . $this->filter->getHash())) {
            return [];
        }
        return [
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class),
        ];
    }
}

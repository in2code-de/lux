<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Analysis\Dashboard;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Browser extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = AnalysisController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Analysis';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        if ($this->cacheLayer->isCacheAvailable('Box/Analysis/Browser/' . $this->filter->getHash())) {
            return [];
        }
        return [
            'browserData' => GeneralUtility::makeInstance(BrowserAmountDataProvider::class, $this->filter),
        ];
    }
}

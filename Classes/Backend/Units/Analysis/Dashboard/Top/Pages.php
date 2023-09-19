<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Analysis\Dashboard\Top;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Pages extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = AnalysisController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Analysis';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        if ($this->cacheLayer->isCacheAvailable('Box/Analysis/TopPages/' . $this->filter->getHash())) {
            return [];
        }
        $pagevisitsRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        return [
            'pages' => $pagevisitsRepository->findCombinedByPageIdentifier($this->filter),
        ];
    }
}

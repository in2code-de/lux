<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Analysis\Dashboard;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Pagevisitsleads extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = AnalysisController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Analysis';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        $pagevisitsRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        return [
            'latestPagevisits' => $pagevisitsRepository->findLatestPagevisits($this->filter),
        ];
    }
}

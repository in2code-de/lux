<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Lead\Dashboard;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\LeadController;
use In2code\Lux\Domain\Repository\VisitorRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Recurring extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = LeadController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Lead';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        return [
            'numberOfRecurringSiteVisitors' => $visitorRepository->findByRecurringSiteVisits($this->filter)->count(),
            'numberOfUniqueSiteVisitors' => $visitorRepository->findByUniqueSiteVisits($this->filter)->count(),
        ];
    }
}

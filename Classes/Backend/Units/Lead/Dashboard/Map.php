<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Lead\Dashboard;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\LeadController;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Map extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = LeadController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Lead';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        $ipinformationRepository = GeneralUtility::makeInstance(IpinformationRepository::class);
        return [
            'countries' => $ipinformationRepository->findAllCountryCodesGrouped($this->filter),
        ];
    }
}

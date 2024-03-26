<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Lead\Dashboard;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Controller\LeadController;
use In2code\Lux\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Identifiedpermonth extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = LeadController::class;
    protected string $cacheLayerFunction = 'dashboardAction';
    protected string $filterClass = 'Lead';
    protected string $filterFunction = 'dashboard';

    protected function assignAdditionalVariables(): array
    {
        if ($this->cacheLayer->isCacheAvailable('Box/Leads/IdentifiedPerMonth/' . $this->filter->getHash())) {
            return [];
        }
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $identifiedLogsFromMonths = $logRepository->findIdentifiedLogsFromMonths($this->filter->setLimit(6));
        return [
            'identifiedPerMonth' => $identifiedLogsFromMonths,
            'dataAvailable' => $this->isDataAvailable($identifiedLogsFromMonths),
        ];
    }

    protected function isDataAvailable(array $data): bool
    {
        foreach ($data as $item) {
            if ($item !== []) {
                return true;
            }
        }
        return false;
    }
}

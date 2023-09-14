<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Pageoverview\Leads;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Hooks\PageOverview;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Body extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = PageOverview::class;
    protected string $cacheLayerFunction = 'render';

    protected function assignAdditionalVariables(): array
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);

        return [
            'pageIdentifier' => $this->getArgument('pageidentifier'),
            'view' => ucfirst(ConfigurationUtility::getPageOverviewView()),
            'visitors' => $visitorRepository->findByVisitedPageIdentifier((int)$this->getArgument('pageidentifier')),
        ];
    }
}

<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units\Pageoverview\Analysis;

use In2code\Lux\Backend\Units\AbstractUnit;
use In2code\Lux\Backend\Units\UnitInterface;
use In2code\Lux\Domain\DataProvider\PageOverview\GotinExternalDataProvider;
use In2code\Lux\Domain\DataProvider\PageOverview\GotinInternalDataProvider;
use In2code\Lux\Domain\DataProvider\PageOverview\GotoutInternalDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Hooks\PageOverview;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Body extends AbstractUnit implements UnitInterface
{
    protected string $cacheLayerClass = PageOverview::class;
    protected string $cacheLayerFunction = 'render';

    protected function assignAdditionalVariables(): array
    {
        $downloadRepository = GeneralUtility::makeInstance(DownloadRepository::class);
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_LAST7DAYS)
            ->setSearchterm($this->getArgument('pageidentifier'));

        return [
            'conversionAmount' => $logRepository->findAmountOfIdentifiedLogsByPageIdentifierAndTimeFrame(
                (int)$this->getArgument('pageidentifier'),
                $filter
            ),
            'downloadAmount' => $downloadRepository->findAmountByPageIdentifierAndTimeFrame(
                (int)$this->getArgument('pageidentifier'),
                $filter
            ),
            'gotinExternal' => GeneralUtility::makeInstance(GotinExternalDataProvider::class, $filter)->get(),
            'gotinInternal' => GeneralUtility::makeInstance(GotinInternalDataProvider::class, $filter)->get(),
            'gotoutInternal' => GeneralUtility::makeInstance(GotoutInternalDataProvider::class, $filter)->get(),
            'linkclickAmount' => $linkclickRepository->getAmountOfLinkclicksByPageIdentifierAndTimeframe(
                (int)$this->getArgument('pageidentifier'),
                $filter
            ),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(
                PagevisistsDataProvider::class,
                ObjectUtility::getFilterDto()->setSearchterm($this->getArgument('pageidentifier'))
            ),
            'pageIdentifier' => $this->getArgument('pageidentifier'),
            'view' => ucfirst(ConfigurationUtility::getPageOverviewView()),
            'visits' => $pagevisitRepository->findAmountPerPage(
                (int)$this->getArgument('pageidentifier'),
                $filter
            ),
            'visitsLastWeek' => $pagevisitRepository->findAmountPerPage(
                (int)$this->getArgument('pageidentifier'),
                ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
            ),
        ];
    }
}

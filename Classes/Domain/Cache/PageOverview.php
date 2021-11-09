<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\DataProvider\PageOverview\GotinExternalDataProvider;
use In2code\Lux\Domain\DataProvider\PageOverview\GotinInternalDataProvider;
use In2code\Lux\Domain\DataProvider\PageOverview\GotoutInternalDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageOverview
 */
class PageOverview extends AbstractLayer implements LayerInterface
{
    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbal
     */
    public function getCachableArguments(): array
    {
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_LAST7DAYS)->setSearchterm($this->identifier);
        $delta = $pagevisitRepository->compareAmountPerPage(
            (int)$this->identifier,
            $filter,
            ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
        );
        return [
            'visitors' => $this->visitorRepository->findByVisitedPageIdentifier((int)$this->identifier),
            'pageIdentifier' => $this->identifier,
            'visits' => $pagevisitRepository->findAmountPerPage((int)$this->identifier, $filter),
            'visitsLastWeek' => $pagevisitRepository->findAmountPerPage(
                (int)$this->identifier,
                ObjectUtility::getFilterDto(FilterDto::PERIOD_7DAYSBEFORELAST7DAYS)
            ),
            'abandons' => $pagevisitRepository->findAbandonsForPage((int)$this->identifier, $filter),
            'delta' => $delta,
            'deltaIconPath' => $delta >= 0 ? 'Icons/increase.svg' : 'Icons/decrease.svg',
            'gotinInternal' => GeneralUtility::makeInstance(
                GotinInternalDataProvider::class,
                $filter
            )->get(),
            'gotinExternal' => GeneralUtility::makeInstance(
                GotinExternalDataProvider::class,
                $filter
            )->get(),
            'gotoutInternal' => GeneralUtility::makeInstance(GotoutInternalDataProvider::class, $filter)->get(),
            'gotout' => '',
            'numberOfVisitorsData' => GeneralUtility::makeInstance(
                PagevisistsDataProvider::class,
                ObjectUtility::getFilterDto()->setSearchterm($this->identifier)
            ),
            'downloadAmount' => $this->downloadRepository->findAmountByPageIdentifierAndTimeFrame(
                (int)$this->identifier,
                $filter
            ),
            'conversionAmount' => $this->logRepository->findAmountOfIdentifiedLogsByPageIdentifierAndTimeFrame(
                (int)$this->identifier,
                $filter
            ),
            'linkclickAmount' => $this->linkclickRepository->getAmountOfLinkclicksByPageIdentifierAndTimeframe(
                (int)$this->identifier,
                $filter
            ),
        ];
    }

    /**
     * @return array
     */
    public function getUncachableArguments(): array
    {
        return [
        ];
    }
}

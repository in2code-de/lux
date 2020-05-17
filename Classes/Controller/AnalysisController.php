<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\DataProvider\AllLinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use In2code\Lux\Domain\DataProvider\DomainDataProvider;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Domain\DataProvider\LanguagesDataProvider;
use In2code\Lux\Domain\DataProvider\LinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class AnalysisController
 */
class AnalysisController extends AbstractController
{
    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function dashboardAction(): void
    {
        $filter = ObjectUtility::getFilterDto();
        $values = [
            'filter' => $filter,
            'numberOfVisitorsData' => ObjectUtility::getObjectManager()->get(PagevisistsDataProvider::class, $filter),
            'numberOfDownloadsData' => ObjectUtility::getObjectManager()->get(DownloadsDataProvider::class, $filter),
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter),
            'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
            'downloads' => $this->downloadRepository->findCombinedByHref($filter),
            'news' => $this->newsvisitRepository->findCombinedByNewsIdentifier($filter),
            'latestPagevisits' => $this->pagevisitsRepository->findLatestPagevisits($filter),
            'browserData' => ObjectUtility::getObjectManager()->get(BrowserAmountDataProvider::class, $filter),
            'linkclickData' => ObjectUtility::getObjectManager()->get(LinkclickDataProvider::class, $filter),
            'languageData' => ObjectUtility::getObjectManager()->get(LanguagesDataProvider::class, $filter),
            'domainData' => ObjectUtility::getObjectManager()->get(DomainDataProvider::class, $filter)
        ];
        $this->view->assignMultiple($values);
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeContentAction(): void
    {
        $this->setFilterExtended();
    }

    /**
     * @param FilterDto $filter
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function contentAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'numberOfVisitorsData' => ObjectUtility::getObjectManager()->get(PagevisistsDataProvider::class, $filter),
            'numberOfDownloadsData' => ObjectUtility::getObjectManager()->get(DownloadsDataProvider::class, $filter),
            'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
            'downloads' => $this->downloadRepository->findCombinedByHref($filter),
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'languageData' => ObjectUtility::getObjectManager()->get(LanguagesDataProvider::class, $filter),
            'domainData' => ObjectUtility::getObjectManager()->get(DomainDataProvider::class, $filter)
        ]);
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeLinkListenerAction(): void
    {
        $this->setFilterExtended();
    }

    /**
     * @param FilterDto $filter
     * @return void
     * @throws Exception
     */
    public function linkListenerAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'linkListeners' => $this->linklistenerRepository->findAll(),
            'allLinkclickData' => ObjectUtility::getObjectManager()->get(AllLinkclickDataProvider::class, $filter),
            'linkclickData' => ObjectUtility::getObjectManager()->get(LinkclickDataProvider::class, $filter),
        ]);
    }

    /**
     * @param Linklistener $linkListener
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function deleteLinkListenerAction(LinkListener $linkListener): void
    {
        $this->linklistenerRepository->remove($linkListener);
        $this->redirect('linkListener');
    }

    /**
     * @param Page $page
     * @return void
     * @throws Exception
     * @throws DBALException
     */
    public function detailPageAction(Page $page): void
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm((string)$page->getUid());
        $this->view->assignMultiple([
            'pagevisits' => $this->pagevisitsRepository->findByPage($page, 100),
            'numberOfVisitorsData' => ObjectUtility::getObjectManager()->get(PagevisistsDataProvider::class, $filter)
        ]);
    }

    /**
     * @param string $href
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function detailDownloadAction(string $href): void
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm(FileUtility::getFilenameFromPathAndFilename($href));
        $this->view->assignMultiple([
            'downloads' => $this->downloadRepository->findByHref($href, 100),
            'numberOfDownloadsData' => ObjectUtility::getObjectManager()->get(DownloadsDataProvider::class, $filter)
        ]);
    }

    /**
     * AJAX action to show a detail view
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @throws DBALException
     * @noinspection PhpUnused
     */
    public function detailAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $filter = $this->getFilterFromSessionForAjaxRequests((string)$request->getQueryParams()['page']);
        /** @var Page $page */
        $page = $this->pageRepository->findByIdentifier((int)$request->getQueryParams()['page']);
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/ContentDetailPageAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'pagevisits' => $page !== null ? $this->pagevisitsRepository->findByPage($page, 10) : null,
            'numberOfVisitorsData' => ObjectUtility::getObjectManager()->get(PagevisistsDataProvider::class, $filter)
        ]);
        $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * AJAX action to show a detail view
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @throws InvalidQueryException
     * @noinspection PhpUnused
     */
    public function detailAjaxDownload(ServerRequestInterface $request): ResponseInterface
    {
        $filter = $this->getFilterFromSessionForAjaxRequests(
            FileUtility::getFilenameFromPathAndFilename((string)$request->getQueryParams()['download'])
        );
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/ContentDetailDownloadAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'downloads' => $this->downloadRepository->findByHref((string)$request->getQueryParams()['download'], 10),
            'numberOfDownloadsData' => ObjectUtility::getObjectManager()->get(DownloadsDataProvider::class, $filter)
        ]);
        $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * @param string $searchterm
     * @return FilterDto
     * @throws Exception
     */
    protected function getFilterFromSessionForAjaxRequests(string $searchterm = ''): FilterDto
    {
        $filterValues = BackendUtility::getSessionValue('filter', 'content', $this->getControllerName());
        $filter = ObjectUtility::getFilterDto();
        if (!empty($searchterm)) {
            $filter->setSearchterm($searchterm);
        }
        if (!empty($filterValues['timeFrom'])) {
            $filter->setTimeFrom((string)$filterValues['timeFrom']);
        }
        if (!empty($filterValues['timeTo'])) {
            $filter->setTimeTo((string)$filterValues['timeTo']);
        }
        if (!empty($filterValues['scoring'])) {
            $filter->setScoring((int)$filterValues['scoring']);
        }
        if (!empty($filterValues['categoryscoring'])) {
            $filter->setCategoryScoring((int)$filterValues['categoryscoring']);
        }
        return $filter;
    }
}

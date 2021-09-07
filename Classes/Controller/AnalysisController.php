<?php
declare(strict_types = 1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\DataProvider\AllLinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use In2code\Lux\Domain\DataProvider\DomainDataProvider;
use In2code\Lux\Domain\DataProvider\DomainNewsDataProvider;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Domain\DataProvider\LanguagesDataProvider;
use In2code\Lux\Domain\DataProvider\LanguagesNewsDataProvider;
use In2code\Lux\Domain\DataProvider\LinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\NewsvisistsDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\DataProvider\SearchDataProvider;
use In2code\Lux\Domain\DataProvider\SocialMediaDataProvider;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
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
            'searchterms' => $this->searchRepository->findCombinedBySearchIdentifier($filter),
            'latestPagevisits' => $this->pagevisitsRepository->findLatestPagevisits($filter),
            'browserData' => ObjectUtility::getObjectManager()->get(BrowserAmountDataProvider::class, $filter),
            'linkclickData' => ObjectUtility::getObjectManager()->get(LinkclickDataProvider::class, $filter),
            'languageData' => ObjectUtility::getObjectManager()->get(LanguagesDataProvider::class, $filter),
            'domainData' => ObjectUtility::getObjectManager()->get(DomainDataProvider::class, $filter),
            'socialMediaData' => ObjectUtility::getObjectManager()->get(SocialMediaDataProvider::class, $filter),
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
     * Page visits and downloads
     *
     * @param FilterDto $filter
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     * @throws DBALException
     */
    public function contentAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'numberOfVisitorsData' => ObjectUtility::getObjectManager()->get(PagevisistsDataProvider::class, $filter),
            'numberOfDownloadsData' => ObjectUtility::getObjectManager()->get(DownloadsDataProvider::class, $filter),
            'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
            'downloads' => $this->downloadRepository->findCombinedByHref($filter),
            'languageData' => ObjectUtility::getObjectManager()->get(LanguagesDataProvider::class, $filter),
            'domainData' => ObjectUtility::getObjectManager()->get(DomainDataProvider::class, $filter),
            'domains' => $this->pagevisitsRepository->getAllDomains($filter)
        ]);
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeNewsAction(): void
    {
        $this->setFilterExtended();
    }

    /**
     * @param FilterDto $filter
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    public function newsAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'newsvisitsData' => ObjectUtility::getObjectManager()->get(NewsvisistsDataProvider::class, $filter),
            'news' => $this->newsvisitRepository->findCombinedByNewsIdentifier($filter),
            'languageData' => ObjectUtility::getObjectManager()->get(LanguagesNewsDataProvider::class, $filter),
            'domainData' => ObjectUtility::getObjectManager()->get(DomainNewsDataProvider::class, $filter),
            'domains' => $this->newsvisitRepository->getAllDomains($filter)
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
     * @throws InvalidQueryException
     */
    public function linkListenerAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'linkListeners' => $this->linklistenerRepository->findByFilter($filter),
            'allLinkclickData' => ObjectUtility::getObjectManager()->get(AllLinkclickDataProvider::class, $filter),
            'linkclickData' => ObjectUtility::getObjectManager()->get(LinkclickDataProvider::class, $filter),
        ]);
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeSearchAction(): void
    {
        $this->setFilterExtended();
    }

    /**
     * @param FilterDto $filter
     * @return void
     * @throws Exception|ExceptionDbal
     */
    public function searchAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'searchData' => ObjectUtility::getObjectManager()->get(SearchDataProvider::class, $filter),
            'search' => $this->searchRepository->findCombinedBySearchIdentifier($filter),
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
     * @param News $news
     * @return void
     * @throws Exception
     * @throws DBALException
     */
    public function detailNewsAction(News $news): void
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm((string)$news->getUid());
        $this->view->assignMultiple([
            'news' => $news,
            'newsvisits' => $this->newsvisitRepository->findByNews($news, 100),
            'newsvisitsData' => ObjectUtility::getObjectManager()->get(NewsvisistsDataProvider::class, $filter)
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
     * @param Linklistener $linkListener
     * @return void
     * @throws Exception
     */
    public function detailLinkListenerAction(Linklistener $linkListener): void
    {
        $filter = $this->getFilterFromSessionForAjaxRequests('linkListener', (string)$linkListener->getUid());
        $this->view->assignMultiple([
            'linkListener' => $linkListener,
            'allLinkclickData' => ObjectUtility::getObjectManager()->get(AllLinkclickDataProvider::class, $filter),
        ]);
    }

    /**
     * @param string $searchterm
     * @return void
     * @throws Exception
     */
    public function detailSearchAction(string $searchterm): void
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm($searchterm);
        $this->view->assignMultiple([
            'searchterm' => $searchterm,
            'searchData' => ObjectUtility::getObjectManager()->get(SearchDataProvider::class, $filter),
            'searches' => $this->searchRepository->findBySearchterm(urldecode($searchterm))
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
        $filter = $this->getFilterFromSessionForAjaxRequests('content', (string)$request->getQueryParams()['page']);
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
     * AJAX action to show a detail view for news
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @throws DBALException
     * @noinspection PhpUnused
     */
    public function detailNewsAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $filter = $this->getFilterFromSessionForAjaxRequests('news', (string)$request->getQueryParams()['news']);
        /** @var News $news */
        $news = $this->newsRepository->findByIdentifier((int)$request->getQueryParams()['news']);
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/NewsDetailPageAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'news' => $news,
            'newsvisits' => $news !== null ? $this->newsvisitRepository->findByNews($news, 10) : null,
            'newsvisitsData' => ObjectUtility::getObjectManager()->get(NewsvisistsDataProvider::class, $filter)
        ]);
        $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * AJAX action to show a detail view for news
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function detailSearchAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/SearchDetailPageAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'searches' => $this->searchRepository->findBySearchterm(
                urldecode($request->getQueryParams()['searchterm'])
            ),
            'searchterm' => $request->getQueryParams()['searchterm']
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
            'content',
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function detailAjaxLinklistener(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Linklistener $linkListener */
        $linkListener = $this->linklistenerRepository->findByIdentifier(
            (int)$request->getQueryParams()['linkListener']
        );
        $filter = $this->getFilterFromSessionForAjaxRequests('linkListener', (string)$linkListener->getUid());
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/LinkListenerAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'linkListener' => $linkListener,
            'linkclicks' => $this->linkclickRepository->findByLinklistenerIdentifier($linkListener->getUid(), 10),
            'allLinkclickData' => ObjectUtility::getObjectManager()->get(AllLinkclickDataProvider::class, $filter)
        ]);
        $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }
}

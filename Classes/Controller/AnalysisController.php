<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
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
use In2code\Lux\Domain\DataProvider\UtmCampaignDataProvider;
use In2code\Lux\Domain\DataProvider\UtmDataProvider;
use In2code\Lux\Domain\DataProvider\UtmMediaDataProvider;
use In2code\Lux\Domain\DataProvider\UtmSourceDataProvider;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class AnalysisController extends AbstractController
{
    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeDashboardAction(): void
    {
        $this->setFilter(FilterDto::PERIOD_LAST3MONTH);
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function dashboardAction(FilterDto $filter): ResponseInterface
    {
        $this->cacheLayer->initialize(__CLASS__, __FUNCTION__);
        $this->view->assignMultiple([
            'cacheLayer' => $this->cacheLayer,
            'filter' => $filter,
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter),
        ]);

        if ($this->cacheLayer->isCacheAvailable('Box/Analysis/Pagevisits/' . $filter->getHash()) === false) {
            $this->view->assignMultiple([
                'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
                'numberOfDownloadsData' => GeneralUtility::makeInstance(DownloadsDataProvider::class, $filter),
                'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
                'downloads' => $this->downloadRepository->findCombinedByHref($filter),
                'news' => $this->newsvisitRepository->findCombinedByNewsIdentifier($filter),
                'searchterms' => $this->searchRepository->findCombinedBySearchIdentifier($filter),
                'browserData' => GeneralUtility::makeInstance(BrowserAmountDataProvider::class, $filter),
                'domainData' => GeneralUtility::makeInstance(DomainDataProvider::class, $filter),
                'socialMediaData' => GeneralUtility::makeInstance(SocialMediaDataProvider::class, $filter),
                'latestPagevisits' => $this->pagevisitsRepository->findLatestPagevisits($filter),
            ]);
        }

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeContentAction(): void
    {
        $this->setFilter();
    }

    /**
     * Page visits and downloads
     *
     * @param FilterDto $filter
     * @param string $export
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws InvalidQueryException
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function contentAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($export === 'csv') {
            return (new ForwardResponse('contentCsv'))->withArguments(['filter' => $filter]);
        }

        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
            'numberOfDownloadsData' => GeneralUtility::makeInstance(DownloadsDataProvider::class, $filter),
            'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
            'downloads' => $this->downloadRepository->findCombinedByHref($filter),
            'languageData' => GeneralUtility::makeInstance(LanguagesDataProvider::class, $filter),
            'domainData' => GeneralUtility::makeInstance(DomainDataProvider::class, $filter),
            'domains' => $this->pagevisitsRepository->getAllDomains($filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws InvalidQueryException
     * @throws ExceptionDbalDriver
     */
    public function contentCsvAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
            'downloads' => $this->downloadRepository->findCombinedByHref($filter),
        ]);
        return $this->csvResponse();
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeNewsAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @param string $export
     * @return ResponseInterface
     * @throws Exception
     * @throws ExceptionDbalDriver
     */
    public function newsAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($export === 'csv') {
            return (new ForwardResponse('newsCsv'))->withArguments(['filter' => $filter]);
        }

        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'newsvisitsData' => GeneralUtility::makeInstance(NewsvisistsDataProvider::class, $filter),
            'news' => $this->newsvisitRepository->findCombinedByNewsIdentifier($filter),
            'languageData' => GeneralUtility::makeInstance(LanguagesNewsDataProvider::class, $filter),
            'domainData' => GeneralUtility::makeInstance(DomainNewsDataProvider::class, $filter),
            'domains' => $this->newsvisitRepository->getAllDomains($filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function newsCsvAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'news' => $this->newsvisitRepository->findCombinedByNewsIdentifier($filter),
        ]);
        return $this->csvResponse();
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeUtmAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @param string $export
     * @return ResponseInterface
     * @throws ExceptionDbalDriver
     * @throws InvalidQueryException
     * @throws DBALException
     */
    public function utmAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($export === 'csv') {
            return (new ForwardResponse('utmCsv'))->withArguments(['filter' => $filter]);
        }

        $variables = [
            'filter' => $filter,
            'utmCampaigns' => $this->utmRepository->findAllCampaigns(),
            'utmSources' => $this->utmRepository->findAllSources(),
            'utmMedia' => $this->utmRepository->findAllMedia(),
            'utmList' => $this->utmRepository->findByFilter($filter),
            'utmData' => GeneralUtility::makeInstance(UtmDataProvider::class, $filter),
            'utmCampaignData' => GeneralUtility::makeInstance(UtmCampaignDataProvider::class, $filter),
            'utmSourceData' => GeneralUtility::makeInstance(UtmSourceDataProvider::class, $filter),
            'utmMediaData' => GeneralUtility::makeInstance(UtmMediaDataProvider::class, $filter),
        ];
        $this->view->assignMultiple($variables);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function utmCsvAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'utmList' => $this->utmRepository->findByFilter($filter),
        ]);
        return $this->csvResponse();
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeLinkListenerAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @param string $export
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws InvalidQueryException
     */
    public function linkListenerAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($export === 'csv') {
            return (new ForwardResponse('linkListenerCsv'))->withArguments(['filter' => $filter]);
        }

        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'linkListeners' => $this->linklistenerRepository->findByFilter($filter),
            'allLinkclickData' => GeneralUtility::makeInstance(AllLinkclickDataProvider::class, $filter),
            'linkclickData' => GeneralUtility::makeInstance(LinkclickDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function linkListenerCsvAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'linkListeners' => $this->linklistenerRepository->findByFilter($filter),
        ]);
        return $this->csvResponse();
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeSearchAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function searchAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'searchData' => GeneralUtility::makeInstance(SearchDataProvider::class, $filter),
            'search' => $this->searchRepository->findCombinedBySearchIdentifier($filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param Linklistener $linkListener
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function deleteLinkListenerAction(LinkListener $linkListener): ResponseInterface
    {
        $this->linklistenerRepository->remove($linkListener);
        return $this->redirect('linkListener');
    }

    /**
     * @param Page $page
     * @return ResponseInterface
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function detailPageAction(Page $page): ResponseInterface
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm((string)$page->getUid());
        $this->view->assignMultiple([
            'pagevisits' => $this->pagevisitsRepository->findByPage($page, 100),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
        ]);
        return $this->htmlResponse();
    }

    /**
     * @param News $news
     * @return ResponseInterface
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function detailNewsAction(News $news): ResponseInterface
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm((string)$news->getUid());
        $this->view->assignMultiple([
            'news' => $news,
            'newsvisits' => $this->newsvisitRepository->findByNews($news, 100),
            'newsvisitsData' => GeneralUtility::makeInstance(NewsvisistsDataProvider::class, $filter),
        ]);
        return $this->htmlResponse();
    }

    /**
     * @param string $href
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function detailDownloadAction(string $href): ResponseInterface
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm(FileUtility::getFilenameFromPathAndFilename($href));
        $this->view->assignMultiple([
            'downloads' => $this->downloadRepository->findByHref($href, 100),
            'numberOfDownloadsData' => GeneralUtility::makeInstance(DownloadsDataProvider::class, $filter),
        ]);
        return $this->htmlResponse();
    }

    /**
     * @param Linklistener $linkListener
     * @return ResponseInterface
     */
    public function detailLinkListenerAction(Linklistener $linkListener): ResponseInterface
    {
        $filter = $this->getFilterFromSessionForAjaxRequests('linkListener', (string)$linkListener->getUid());
        $this->view->assignMultiple([
            'linkListener' => $linkListener,
            'allLinkclickData' => GeneralUtility::makeInstance(AllLinkclickDataProvider::class, $filter),
        ]);
        return $this->htmlResponse();
    }

    /**
     * @param string $searchterm
     * @return ResponseInterface
     */
    public function detailSearchAction(string $searchterm): ResponseInterface
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm($searchterm);
        $this->view->assignMultiple([
            'searchterm' => $searchterm,
            'searchData' => GeneralUtility::makeInstance(SearchDataProvider::class, $filter),
            'searches' => $this->searchRepository->findBySearchterm(urldecode($searchterm)),
        ]);
        return $this->htmlResponse();
    }

    /**
     * AJAX action to show a detail view
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     * @throws DBALException
     * @throws ExceptionDbalDriver
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
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
        ]);
        $response = GeneralUtility::makeInstance(JsonResponse::class);
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
     * @noinspection PhpUnused
     * @throws DBALException
     * @throws ExceptionDbalDriver
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
            'newsvisitsData' => GeneralUtility::makeInstance(NewsvisistsDataProvider::class, $filter),
        ]);
        $response = GeneralUtility::makeInstance(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * AJAX action to show a detail view for utm
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function detailUtmAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $leadController = GeneralUtility::makeInstance(LeadController::class);
        return $leadController->detailAjax($request);
    }

    /**
     * AJAX action to show a detail view for news
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
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
            'searchterm' => $request->getQueryParams()['searchterm'],
        ]);
        $response = GeneralUtility::makeInstance(JsonResponse::class);
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
            'numberOfDownloadsData' => GeneralUtility::makeInstance(DownloadsDataProvider::class, $filter),
        ]);
        $response = GeneralUtility::makeInstance(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
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
            'allLinkclickData' => GeneralUtility::makeInstance(AllLinkclickDataProvider::class, $filter),
        ]);
        $response = GeneralUtility::makeInstance(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * @return void
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    protected function addDocumentHeaderForCurrentController(): void
    {
        $actions = ['dashboard', 'content', 'utm', 'linkListener'];
        if ($this->newsvisitRepository->isTableFilled()) {
            $actions[] = 'news';
        }
        if ($this->searchRepository->isTableFilled()) {
            $actions[] = 'search';
        }
        $menuConfiguration = [];
        foreach ($actions as $action) {
            $menuConfiguration[$action] = LocalizationUtility::translate(
                'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.analysis.' . $action
            );
        }
        $this->addDocumentHeader($menuConfiguration);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\DataProvider\AllLinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\DomainNewsDataProvider;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Domain\DataProvider\LanguagesDataProvider;
use In2code\Lux\Domain\DataProvider\LanguagesNewsDataProvider;
use In2code\Lux\Domain\DataProvider\LinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\NewsvisistsDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\DataProvider\ReferrerAmountDataProvider;
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
use In2code\Lux\Exception\ArgumentsException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
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
     * @throws ExceptionDbal
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws ExceptionDbalDriver
     */
    public function dashboardAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter),
        ]);

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
     * @throws ExceptionDbalDriver
     * @throws InvalidQueryException
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
            'referrerAmountData' => GeneralUtility::makeInstance(ReferrerAmountDataProvider::class, $filter),
            'socialMediaData' => GeneralUtility::makeInstance(SocialMediaDataProvider::class, $filter),
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
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws ExceptionDbal
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
     * @throws ExceptionDbal
     */
    public function utmAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($export === 'csv') {
            return (new ForwardResponse('utmCsv'))->withArguments(['filter' => $filter]);
        }

        $variables = [
            'filter' => $filter,
            'utmCampaigns' => $this->utmRepository->findAllCampaigns($filter),
            'utmSources' => $this->utmRepository->findAllSources($filter),
            'utmMedia' => $this->utmRepository->findAllMedia($filter),
            'utmContent' => $this->utmRepository->findAllContent($filter),
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
     */
    public function deleteLinkListenerAction(LinkListener $linkListener): ResponseInterface
    {
        $this->linklistenerRepository->remove($linkListener);
        return $this->redirect('linkListener');
    }

    /**
     * @param Page $page
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws ArgumentsException
     */
    public function detailPageAction(Page $page): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession(
            'content',
            $this->getControllerName(),
            ['searchterm' => (string)$page->getUid(), 'limit' => 100]
        );
        $this->view->assignMultiple([
            'filter' => $filter,
            'pagevisits' => $this->pagevisitsRepository->findByFilter($filter),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param News $news
     * @return ResponseInterface
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public function detailNewsAction(News $news): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession(
            'news',
            $this->getControllerName(),
            ['searchterm' => $news->getUid(), 'limit' => 100]
        );
        $this->view->assignMultiple([
            'filter' => $filter,
            'news' => $news,
            'newsvisits' => $this->newsvisitRepository->findByFilter($filter),
            'newsvisitsData' => GeneralUtility::makeInstance(NewsvisistsDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param string $href
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     * @throws InvalidQueryException
     */
    public function detailDownloadAction(string $href): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession(
            'content',
            $this->getControllerName(),
            ['href' => $href, 'limit' => 100]
        );
        $this->view->assignMultiple([
            'filter' => $filter,
            'downloads' => $this->downloadRepository->findByFilter($filter),
            'numberOfDownloadsData' => GeneralUtility::makeInstance(DownloadsDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param Linklistener $linkListener
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function detailLinkListenerAction(Linklistener $linkListener): ResponseInterface
    {
        $filter = $this->getFilterFromSessionForAjaxRequests('linkListener', (string)$linkListener->getUid());
        $this->view->assignMultiple([
            'linkListener' => $linkListener,
            'allLinkclickData' => GeneralUtility::makeInstance(AllLinkclickDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param string $searchterm
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function detailSearchAction(string $searchterm): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession(
            'search',
            $this->getControllerName(),
            ['searchterm' => $searchterm, 'limit' => 100]
        );
        $this->view->assignMultiple([
            'filter' => $filter,
            'searchterm' => $searchterm,
            'searchData' => GeneralUtility::makeInstance(SearchDataProvider::class, $filter),
            'searches' => $this->searchRepository->findByFilter($filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * AJAX action to show a detail view coming from contentAction
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     * @throws ExceptionDbal
     * @throws ArgumentsException
     */
    public function detailAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession(
            'content',
            'Analysis',
            ['searchterm' => (string)$request->getQueryParams()['page'], 'limit' => 10]
        );
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/ContentDetailPageAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'pagevisits' => $this->pagevisitsRepository->findByFilter($filter),
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
     * @throws ExceptionDbal
     */
    public function detailNewsAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession(
            'news',
            'Analysis',
            ['searchterm' => (string)$request->getQueryParams()['news'], 'limit' => 10]
        );
        /** @var News $news */
        $news = $this->newsRepository->findByIdentifier((int)$request->getQueryParams()['news']);
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/NewsDetailPageAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'news' => $news,
            'newsvisits' => $this->newsvisitRepository->findByFilter($filter),
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
     * @throws Exception
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
        $filter = BackendUtility::getFilterFromSession(
            'search',
            'Analysis',
            ['searchterm' => urldecode($request->getQueryParams()['searchterm']), 'limit' => 10]
        );
        $standaloneView->assignMultiple([
            'searches' => $this->searchRepository->findByFilter($filter),
            'searchterm' => $request->getQueryParams()['searchterm'],
            'searchData' => GeneralUtility::makeInstance(SearchDataProvider::class, $filter),
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
        $filter = BackendUtility::getFilterFromSession(
            'content',
            'Analysis',
            [
                'href' => (string)$request->getQueryParams()['download'],
                'limit' => 10,
            ]
        );
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/ContentDetailDownloadAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'downloads' => $this->downloadRepository->findByFilter($filter),
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
        $actions = ['dashboard', 'content'];
        if ($this->newsvisitRepository->isTableFilled()) {
            $actions[] = 'news';
        }
        if ($this->searchRepository->isTableFilled()) {
            $actions[] = 'search';
        }
        $actions = array_merge($actions, ['utm', 'linkListener']);
        $menuConfiguration = [];
        foreach ($actions as $action) {
            $menuConfiguration[] = [
                'action' => $action,
                'label' => LocalizationUtility::translate(
                    'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.analysis.' . $action
                ),
            ];
        }
        $this->addDocumentHeader($menuConfiguration);
    }
}

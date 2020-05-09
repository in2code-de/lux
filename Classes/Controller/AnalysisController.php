<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use In2code\Lux\Domain\DataProvider\DomainDataProvider;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Domain\DataProvider\LanguagesDataProvider;
use In2code\Lux\Domain\DataProvider\LinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * @throws DBALException
     * @throws Exception
     */
    public function informationAction(): void
    {
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR);
        $values = [
            'countries' => $this->ipinformationRepository->findAllCountryCodesGrouped($filter),
            'statistics' => [
                'visitors' => $this->visitorRepository->findAllAmount(),
                'identified' => $this->visitorRepository->findAllIdentifiedAmount(),
                'unknown' => $this->visitorRepository->findAllUnknownAmount(),
                'identifiedEmail4Link' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_EMAIL4LINK, $filter),
                'identifiedFieldListening' => $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED, $filter),
                'identifiedFormListening' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_FORMLISTENING, $filter),
                'identifiedFrontendLogin' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION, $filter),
                'identifiedLuxletter' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_LUXLETTERLINK, $filter),
                'luxcategories' => $this->categoryRepository->findAllAmount(),
                'pagevisits' => $this->pagevisitsRepository->findAllAmount(),
                'downloads' => $this->downloadRepository->findAllAmount(),
                'versionLux' => ExtensionUtility::getLuxVersion(),
                'versionLuxenterprise' => ExtensionUtility::getLuxenterpriseVersion(),
                'versionLuxletter' => ExtensionUtility::getLuxletterVersion(),
                'linkclicks' => $this->linkclickRepository->findAllAmount(),
                'fingerprints' => $this->fingerprintRepository->findAllAmount(),
                'ipinformations' => $this->ipinformationRepository->findAllAmount(),
                'logs' => $this->logRepository->findAllAmount()
            ]
        ];
        $this->view->assignMultiple($values);
    }

    /**
     * @param Page $page
     * @return void
     * @throws Exception
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
     * @noinspection PhpUnused
     */
    public function detailAjaxPage(ServerRequestInterface $request): ResponseInterface
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm($request->getQueryParams()['page']);
        /** @var Page $page */
        $page = $this->pageRepository->findByIdentifier((int)$request->getQueryParams()['page']);
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/ContentDetailPageAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'pagevisits' => $this->pagevisitsRepository->findByPage($page, 10),
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
     * @noinspection PhpUnused
     */
    public function detailAjaxDownload(ServerRequestInterface $request): ResponseInterface
    {
        $filter = ObjectUtility::getFilterDto()->setSearchterm(
            FileUtility::getFilenameFromPathAndFilename($request->getQueryParams()['download'])
        );
        $standaloneView = ObjectUtility::getStandaloneView();
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Analysis/ContentDetailDownloadAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'downloads' => $this->downloadRepository->findByHref($request->getQueryParams()['download'], 10),
            'numberOfDownloadsData' => ObjectUtility::getObjectManager()->get(DownloadsDataProvider::class, $filter)
        ]);
        $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }
}

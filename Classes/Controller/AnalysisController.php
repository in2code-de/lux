<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Domain\DataProvider\LinkclickDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\FingerprintRepository;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Lux\Utility\ObjectUtility;
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
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * @var IpinformationRepository
     */
    protected $ipinformationRepository = null;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

    /**
     * @var PagevisitRepository
     */
    protected $pagevisitsRepository = null;

    /**
     * @var DownloadRepository
     */
    protected $downloadRepository = null;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * AnalysisController constructor.
     * @param VisitorRepository $visitorRepository
     * @param IpinformationRepository $ipinformationRepository
     * @param LogRepository $logRepository
     * @param PagevisitRepository $pagevisitsRepository
     * @param DownloadRepository $downloadRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        VisitorRepository $visitorRepository,
        IpinformationRepository $ipinformationRepository,
        LogRepository $logRepository,
        PagevisitRepository $pagevisitsRepository,
        DownloadRepository $downloadRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->ipinformationRepository = $ipinformationRepository;
        $this->logRepository = $logRepository;
        $this->pagevisitsRepository = $pagevisitsRepository;
        $this->downloadRepository = $downloadRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return void
     * @throws InvalidQueryException
     * @throws Exception
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
            'latestPagevisits' => $this->pagevisitsRepository->findLatestPagevisits($filter),
            'browserData' => ObjectUtility::getObjectManager()->get(BrowserAmountDataProvider::class, $filter),
            'linkclickData' => ObjectUtility::getObjectManager()->get(LinkclickDataProvider::class, $filter)
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
            'luxCategories' => $this->categoryRepository->findAllLuxCategories()
        ]);
    }

    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    public function informationAction(): void
    {
        $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        $fingerprintRepo = ObjectUtility::getObjectManager()->get(FingerprintRepository::class);
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
                'linkclicks' => $linkclickRepository->findAllAmount(),
                'fingerprints' => $fingerprintRepo->findAllAmount(),
                'ipinformations' => $this->ipinformationRepository->findAllAmount(),
                'logs' => $this->logRepository->findAllAmount()
            ]
        ];
        $this->view->assignMultiple($values);
    }

    /**
     * @param Page $page
     * @return void
     */
    public function detailPageAction(Page $page): void
    {
        $this->view->assignMultiple([
            'pagevisits' => $this->pagevisitsRepository->findByPage($page)
        ]);
    }

    /**
     * @param string $href
     * @return void
     */
    public function detailDownloadAction(string $href): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->view->assignMultiple([
            'downloads' => $this->downloadRepository->findByHref($href)
        ]);
    }
}

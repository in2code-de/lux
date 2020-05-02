<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\DataProvider\IdentificationMethodsDataProvider;
use In2code\Lux\Domain\DataProvider\ReferrerAmountDataProvider;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class LeadController
 */
class LeadController extends AbstractController
{
    /**
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * @var PagevisitRepository
     */
    protected $pagevisitsRepository = null;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentNameException
     */
    public function initializeDashboardAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @return void
     * @throws Exception
     * @throws InvalidQueryException
     * @throws DBALException
     */
    public function dashboardAction(FilterDto $filter): void
    {
        $identificationMDP = ObjectUtility::getObjectManager()->get(IdentificationMethodsDataProvider::class, $filter);
        $referrerAmountDP = ObjectUtility::getObjectManager()->get(ReferrerAmountDataProvider::class, $filter);
        $values = [
            'filter' => $filter,
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter, 10),
            'numberOfUniqueSiteVisitors' => $this->visitorRepository->findByUniqueSiteVisits($filter)->count(),
            'numberOfRecurringSiteVisitors' => $this->visitorRepository->findByRecurringSiteVisits($filter)->count(),
            'hottestVisitors' => $this->visitorRepository->findByHottestScorings($filter, 8),
            'numberOfIdentifiedVisitors' => $this->visitorRepository->findIdentified($filter)->count(),
            'identifiedPerMonth' => $this->logRepository->findIdentifiedLogsFromMonths(6),
            'numberOfUnknownVisitors' => $this->visitorRepository->findUnknown($filter)->count(),
            'identificationMethods' => $identificationMDP,
            'referrerAmountData' => $referrerAmountDP,
            'whoisonline' => $this->visitorRepository->findOnline(8)
        ];
        $this->view->assignMultiple($values);
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeListAction(): void
    {
        $this->setFilterExtended();
    }

    /**
     * @param FilterDto $filter
     * @param string $export
     * @return void
     * @throws StopActionException
     * @throws InvalidQueryException
     */
    public function listAction(FilterDto $filter, string $export = ''): void
    {
        if ($export === 'csv') {
            $this->forward('downloadCsv', null, null, ['filter' => $filter]);
        }
        $this->view->assignMultiple([
            'hottestVisitors' => $this->visitorRepository->findByHottestScorings($filter),
            'filter' => $filter,
            'allVisitors' => $this->visitorRepository->findAllWithIdentifiedFirst($filter),
            'identifiedByMostVisits' => $this->visitorRepository->findIdentifiedByMostVisits($filter),
            'numberOfVisitorsByDay' => $this->pagevisitsRepository->getNumberOfVisitorsByDay(),
            'luxCategories' => $this->categoryRepository->findAllLuxCategories()
        ]);
    }

    /**
     * @param FilterDto $filter
     * @return void
     * @throws InvalidQueryException
     */
    public function downloadCsvAction(FilterDto $filter): void
    {
        $this->view->assignMultiple([
            'allVisitors' => $this->visitorRepository->findAllWithIdentifiedFirst($filter),
        ]);

        $this->response->setHeader('Content-Type', 'text/x-csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="Leads.csv"');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->sendHeaders();
        echo $this->view->render();
        exit;
    }

    /**
     * @return void
     * @throws StopActionException
     */
    public function resetFilterForListAction(): void
    {
        BackendUtility::saveValueToSession('filter', []);
        $this->redirect('list');
    }

    /**
     * @param Visitor $visitor
     * @return void
     */
    public function detailAction(Visitor $visitor): void
    {
        $this->view->assign('visitor', $visitor);
    }

    /**
     * Really remove visitor completely from db (not only deleted=1)
     *
     * @param Visitor $visitor
     * @return void
     * @throws StopActionException
     * @throws DBALException
     */
    public function removeAction(Visitor $visitor): void
    {
        $this->visitorRepository->removeVisitor($visitor);
        $this->visitorRepository->removeRelatedTableRowsByVisitor($visitor);
        $this->addFlashMessage('Visitor completely removed from database');
        $this->redirect('list');
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnknownObjectException
     * @throws DBALException
     * @throws Exception
     */
    public function deactivateAction(Visitor $visitor): void
    {
        $visitor->setBlacklistedStatus();
        $this->visitorRepository->update($visitor);
        $this->addFlashMessage('Visitor is blacklisted now');
        $this->redirect('list');
    }

    /**
     * AJAX action to show a detail view
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface Todo: Second parameter is removed in TYPO3 10
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function detailAjax(ServerRequestInterface $request, ResponseInterface $response = null): ResponseInterface
    {
        if ($response === null) {
            $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        }
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Lead/DetailAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'visitor' => $visitorRepository->findByUid((int)$request->getQueryParams()['visitor'])
        ]);
        $response->getBody()->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response Todo: Second parameter is removed in TYPO3 10
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function detailDescriptionAjax(
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ): ResponseInterface {
        if ($response === null) {
            $response = ObjectUtility::getObjectManager()->get(JsonResponse::class);
        }
        /** @var VisitorRepository $visitorRepository */
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $visitor = $visitorRepository->findByUid((int)$request->getQueryParams()['visitor']);
        $visitor->setDescription($request->getQueryParams()['value']);
        $visitorRepository->update($visitor);
        $visitorRepository->persistAll();
        return $response;
    }

    /**
     * @param VisitorRepository $visitorRepository
     * @return void
     */
    public function injectFormRepository(VisitorRepository $visitorRepository): void
    {
        $this->visitorRepository = $visitorRepository;
    }

    /**
     * @param PagevisitRepository $pagevisitRepository
     * @return void
     */
    public function injectPagevisitRepository(PagevisitRepository $pagevisitRepository): void
    {
        $this->pagevisitsRepository = $pagevisitRepository;
    }

    /**
     * @param CategoryRepository $categoryRepository
     * @return void
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param LogRepository $logRepository
     * @return void
     */
    public function injectLogRepository(LogRepository $logRepository): void
    {
        $this->logRepository = $logRepository;
    }
}

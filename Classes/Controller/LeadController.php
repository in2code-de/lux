<?php
declare(strict_types = 1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\DataProvider\IdentificationMethodsDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\DataProvider\ReferrerAmountDataProvider;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class LeadController
 * Todo: Return type ": ResponseInterface" and "return $this->htmlResponse();" when TYPO3 10 support is dropped
 *       for all actions
 */
class LeadController extends AbstractController
{
    /**
     * @return void
     * @throws ConfigurationException
     * @throws DBALException
     * @throws InvalidQueryException
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function dashboardAction(): void
    {
        $filter = ObjectUtility::getFilterDto();
        $this->cacheLayer->initialize(__CLASS__, __FUNCTION__);
        $this->view->assignMultiple([
            'cacheLayer' => $this->cacheLayer,
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter, 10),
            'whoisonline' => $this->visitorRepository->findOnline(8),
        ]);

        if ($this->cacheLayer->isCacheAvailable('Box/Leads/Recurring') === false) {
            $this->view->assignMultiple([
                'filter' => $filter,
                'numberOfUniqueSiteVisitors' => $this->visitorRepository->findByUniqueSiteVisits($filter)->count(),
                'numberOfRecurringSiteVisitors' =>
                    $this->visitorRepository->findByRecurringSiteVisits($filter)->count(),
                'identifiedPerMonth' => $this->logRepository->findIdentifiedLogsFromMonths(6),
                'numberOfIdentifiedVisitors' => $this->visitorRepository->findIdentified($filter)->count(),
                'numberOfUnknownVisitors' => $this->visitorRepository->findUnknown($filter)->count(),
                'identificationMethods' =>
                    GeneralUtility::makeInstance(IdentificationMethodsDataProvider::class, $filter),
                'referrerAmountData' => GeneralUtility::makeInstance(ReferrerAmountDataProvider::class, $filter),
                'countries' => $this->ipinformationRepository->findAllCountryCodesGrouped($filter),
                'hottestVisitors' => $this->visitorRepository->findByHottestScorings($filter, 10),
                'renderingTime' => $this->renderingTimeService->getTime(),
            ]);
        }
    }

    /**
     * @return void
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
     * @throws InvalidQueryException
     * @throws StopActionException
     */
    public function listAction(FilterDto $filter, string $export = ''): void
    {
        if ($export === 'csv') {
            $this->forward('downloadCsv', null, null, ['filter' => $filter]);
        }
        $this->view->assignMultiple([
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
            'hottestVisitors' => $this->visitorRepository->findByHottestScorings($filter, 8),
            'filter' => $filter,
            'allVisitors' => $this->visitorRepository->findAllWithIdentifiedFirst($filter),
            'luxCategories' => $this->categoryRepository->findAllLuxCategories()
        ]);
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function downloadCsvAction(FilterDto $filter)
    {
        $this->view->assignMultiple([
            'allVisitors' => $this->visitorRepository->findAllWithIdentifiedFirst($filter)
        ]);
        return $this->csvResponse($this->view->render());
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
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function detailAjax(ServerRequestInterface $request): ResponseInterface
    {
        $response = GeneralUtility::makeInstance(JsonResponse::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Lead/ListDetailAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $standaloneView->assignMultiple([
            'visitor' => $visitorRepository->findByUid((int)$request->getQueryParams()['visitor'])
        ]);
        /** @var StreamInterface $stream */
        $stream = $response->getBody();
        $stream->write(json_encode(['html' => $standaloneView->render()]));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @noinspection PhpUnused
     */
    public function detailDescriptionAjax(ServerRequestInterface $request): ResponseInterface
    {
        $response = GeneralUtility::makeInstance(JsonResponse::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        /** @var Visitor $visitor */
        $visitor = $visitorRepository->findByUid((int)$request->getQueryParams()['visitor']);
        $visitor->setDescription($request->getQueryParams()['value']);
        $visitorRepository->update($visitor);
        $visitorRepository->persistAll();
        return $response;
    }
}

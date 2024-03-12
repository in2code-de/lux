<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\DataProvider\CompanyAmountPerMonthDataProvider;
use In2code\Lux\Domain\DataProvider\CompanyCategoryScoringsDataProvider;
use In2code\Lux\Domain\DataProvider\CompanyScoringWeeksDataProvider;
use In2code\Lux\Domain\DataProvider\LeadsPerTimeDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\DataProvider\RevenueClassDataProvider;
use In2code\Lux\Domain\DataProvider\VisitorCategoryScoringsDataProvider;
use In2code\Lux\Domain\DataProvider\VisitorScoringWeeksDataProvider;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\CompanyRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\CompanyConfigurationService;
use In2code\Lux\Exception\AuthenticationException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Fluid\View\StandaloneView;

class LeadController extends AbstractController
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
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     */
    public function dashboardAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter->setLimit(10)),
            'whoisonline' => $this->visitorRepository->findOnline($filter->setLimit(8)),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeListAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @param string $export
     * @return ResponseInterface
     * @throws ExceptionDbal
     * @throws InvalidQueryException
     */
    public function listAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($export === 'csv') {
            return (new ForwardResponse('downloadCsv'))->withArguments(['filter' => $filter]);
        }
        $this->view->assignMultiple([
            'filter' => $filter->setLimit(750),
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'allVisitors' => $this->visitorRepository->findAllWithIdentifiedFirst($filter->setLimit(750)),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
            'hottestVisitors' => $this->visitorRepository->findByHottestScorings($filter->setLimit(8)),
            'visitorsPerTimeData' => GeneralUtility::makeInstance(LeadsPerTimeDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param Visitor $visitor
     * @return ResponseInterface
     * @throws AuthenticationException
     */
    public function detailAction(Visitor $visitor): ResponseInterface
    {
        if ($visitor->canBeRead() === false) {
            throw new AuthenticationException('Not allowed to view this visitor', 1709071863);
        }
        $filter = ObjectUtility::getFilterDtoFromStartAndEnd($visitor->getDateOfPagevisitFirst(), new DateTime())
            ->setVisitor($visitor);
        $this->view->assignMultiple([
            'visitor' => $visitor,
            'companies' => $this->companyRepository->findAll(),
            'scoringWeeks' => GeneralUtility::makeInstance(VisitorScoringWeeksDataProvider::class, $filter),
            'categoryScorings' => GeneralUtility::makeInstance(VisitorCategoryScoringsDataProvider::class, $filter),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
        ]);

        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function downloadCsvAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'allVisitors' => $this->visitorRepository->findAllWithIdentifiedFirst($filter->setLimit(750)),
        ]);
        return $this->csvResponse($this->view->render());
    }

    /**
     * Really remove visitor completely from db (not only deleted=1)
     *
     * @param Visitor $visitor
     * @return ResponseInterface
     * @throws ExceptionDbal
     */
    public function removeAction(Visitor $visitor): ResponseInterface
    {
        $this->visitorRepository->removeVisitor($visitor);
        $this->addFlashMessage('Visitor completely removed from database');
        return $this->redirect('list');
    }

    /**
     * @param Visitor $visitor
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function deactivateAction(Visitor $visitor): ResponseInterface
    {
        $visitor->setBlacklistedStatus();
        $this->visitorRepository->update($visitor);
        $this->addFlashMessage('Visitor is blacklisted now');
        return $this->redirect('list');
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeCompaniesAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @param string $export
     * @return ResponseInterface
     * @throws ExceptionDbal
     */
    public function companiesAction(FilterDto $filter, string $export = ''): ResponseInterface
    {
        if ($this->isCompaniesViewDisabled()) {
            return new ForwardResponse('companiesDisabled');
        }
        if ($export === 'csv') {
            return (new ForwardResponse('downloadCsvCompanies'))->withArguments(['filter' => $filter]);
        }

        $limit = (int)($this->settings['tracking']['company']['connectionLimit'] ?? 0);
        $statistics = $this->wiredmindsRepository->getStatus()[0] ?? ['hits' => 0, 'misses' => 0];
        $available = $limit - $statistics['hits'] - $statistics['misses'];
        if ($available < 0) {
            $available = 0;
        }
        $this->view->assignMultiple([
            'filter' => $filter,
            'countries' => $this->companyRepository->findCountriesByFilter($filter),
            'luxCategories' => $this->categoryRepository->findAllLuxCategories(),
            'branches' => $this->companyRepository->findAllBranches($filter),
            'categories' => $this->categoryRepository->findAllLuxCompanyCategories(),
            'companies' => $this->companyRepository->findByFilter($filter->setLimit(750)),
            'revenueClassData' => GeneralUtility::makeInstance(RevenueClassDataProvider::class, $filter),
            'companyAmountPerMonthData' => GeneralUtility::makeInstance(
                CompanyAmountPerMonthDataProvider::class,
                $filter
            ),
            'latestPagevisitsWithCompanies' => $this->pagevisitsRepository->findLatestPagevisitsWithCompanies(
                $filter->setLimit(8)
            ),
            'wiredminds' => [
                'status' => $this->wiredmindsRepository->getStatus() !== [],
                'statistics' => $statistics,
                'limit' => $limit,
                'overall' => $limit,
                'available' => $available,
                'show' => BackendUtility::isAdministrator(),
            ],
        ]);
        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    public function companiesDisabledAction(string $tokenWiredminds = ''): ResponseInterface
    {
        if ($tokenWiredminds !== '') {
            try {
                $companyConfigurationService = GeneralUtility::makeInstance(CompanyConfigurationService::class);
                $companyConfigurationService->add($tokenWiredminds);
                $this->addFlashMessage(LocalizationUtility::translateByKey('module.companiesDisabled.token.success'));
            } catch (Throwable $exception) {
                // Todo: AbstractMessage::ERROR can be replaced with ContextualFeedbackSeverity::ERROR when TYPO3 11 support is dropped
                $this->addFlashMessage($exception->getMessage(), '', AbstractMessage::ERROR);
            }
            return $this->redirect('companies');
        }
        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param Company $company
     * @return ResponseInterface
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws AuthenticationException
     */
    public function companyAction(Company $company): ResponseInterface
    {
        if ($company->canBeRead() === false) {
            throw new AuthenticationException('Not allowed to view this company', 1709123396);
        }
        $filter = ObjectUtility::getFilterDtoFromStartAndEnd(
            $company->getFirstPagevisit()->getCrdate(),
            new DateTime()
        )->setCompany($company);
        $this->view->assignMultiple([
            'company' => $company,
            'categories' => $this->categoryRepository->findAllLuxCompanyCategories(),
            'interestingLogs' => $this->logRepository->findInterestingLogsByCompany($company, $filter->setLimit(250)),
            'scoringWeeks' => GeneralUtility::makeInstance(CompanyScoringWeeksDataProvider::class, $filter),
            'categoryScorings' => GeneralUtility::makeInstance(CompanyCategoryScoringsDataProvider::class, $filter),
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
        ]);
        $this->addDocumentHeaderForCurrentController();
        return $this->defaultRendering();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     * @throws ExceptionDbal
     */
    public function downloadCsvCompaniesAction(FilterDto $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'companies' => $this->companyRepository->findByFilter($filter->setLimit(750)),
        ]);
        return $this->csvResponse($this->view->render());
    }

    /**
     * Really remove visitor completely from db (not only deleted=1)
     *
     * @param Company $company
     * @param bool $removeVisitors
     * @return ResponseInterface
     * @throws ExceptionDbal
     */
    public function removeCompanyAction(Company $company, bool $removeVisitors = false): ResponseInterface
    {
        $this->companyRepository->removeCompany($company, $removeVisitors);
        $this->addFlashMessage('Company completely removed from database');
        return $this->redirect('companies');
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     * @throws Exception
     */
    public function detailAjax(ServerRequestInterface $request): ResponseInterface
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Lead/ListDetailAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        /** @var Visitor $visitor */
        $visitor = $visitorRepository->findByUid((int)$request->getQueryParams()['visitor']);
        if ($visitor->canBeRead() === false) {
            throw new AuthenticationException('Not allowed to view this visitor', 1709072495);
        }
        $filter = ObjectUtility::getFilterDtoFromStartAndEnd($visitor->getDateOfPagevisitFirst(), new DateTime())
            ->setVisitor($visitor);
        $standaloneView->assignMultiple([
            'visitor' => $visitor,
            'company' => $visitor->getCompanyrecord(),
            'companies' => $companyRepository->findAll(),
            'scoringWeeks' => GeneralUtility::makeInstance(VisitorScoringWeeksDataProvider::class, $filter),
        ]);
        return $this->jsonResponse(json_encode(['html' => $standaloneView->render()]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     * @throws AuthenticationException
     */
    public function detailCompaniesAjax(ServerRequestInterface $request): ResponseInterface
    {
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:lux/Resources/Private/Templates/Lead/CompanyListDetailAjax.html'
        ));
        $standaloneView->setPartialRootPaths(['EXT:lux/Resources/Private/Partials/']);
        $company = $companyRepository->findByUid((int)$request->getQueryParams()['company']);
        if ($company->canBeRead() === false) {
            throw new AuthenticationException('Not allowed to view this company', 1709123966);
        }
        $standaloneView->assignMultiple([
            'company' => $company,
            'visitors' => $visitorRepository->findByCompany($company, 6),
            'companies' => $companyRepository->findAll(),
            'categories' => $categoryRepository->findAllLuxCompanyCategories(),
        ]);
        return $this->jsonResponse(json_encode(['html' => $standaloneView->render()]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function companiesInformationAjax(ServerRequestInterface $request): ResponseInterface
    {
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        /** @var Company $company */
        $company = $companyRepository->findByUid((int)$request->getQueryParams()['company']);
        $result = [
            'numberOfVisits' => $company->getNumberOfVisits(),
            'numberOfVisitors' => $company->getNumberOfVisitors(),
        ];
        return $this->jsonResponse(json_encode($result));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @noinspection PhpUnused
     */
    public function detailDescriptionAjax(ServerRequestInterface $request): ResponseInterface
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        /** @var Visitor $visitor */
        $visitor = $visitorRepository->findByUid((int)$request->getQueryParams()['visitor']);
        $visitor->setDescription($request->getQueryParams()['value']);
        $visitorRepository->update($visitor);
        $visitorRepository->persistAll();
        return GeneralUtility::makeInstance(JsonResponse::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @noinspection PhpUnused
     */
    public function detailCompanydescriptionAjax(ServerRequestInterface $request): ResponseInterface
    {
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        /** @var Company $company */
        $company = $companyRepository->findByUid((int)$request->getQueryParams()['company']);
        $company->setDescription($request->getQueryParams()['value']);
        $companyRepository->update($company);
        $companyRepository->persistAll();
        return GeneralUtility::makeInstance(JsonResponse::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @noinspection PhpUnused
     */
    public function detailCompanyrecordAjax(ServerRequestInterface $request): ResponseInterface
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        /** @var Visitor $visitor */
        $visitor = $visitorRepository->findByUid((int)$request->getQueryParams()['visitor']);
        $company = $this->companyRepository->findByUid((int)$request->getQueryParams()['value']);
        if ($visitor !== null && $company !== null) {
            $visitor->setCompanyrecord($company);
            $visitorRepository->update($visitor);
            $visitorRepository->persistAll();
        }
        return GeneralUtility::makeInstance(JsonResponse::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @noinspection PhpUnused
     */
    public function setCategoryToCompanyAjax(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Company $company */
        $company = $this->companyRepository->findByUid((int)$request->getQueryParams()['company']);
        $category = $this->categoryRepository->findByUid((int)$request->getQueryParams()['value']);
        if ($company !== null && $category !== null) {
            $company->setCategory($category);
            $this->companyRepository->update($company);
            $this->companyRepository->persistAll();
        }
        return GeneralUtility::makeInstance(JsonResponse::class);
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function getOverallLeadsAjax(): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession('list', 'Lead');
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $amount = $visitorRepository->findAllWithIdentifiedFirstAmount($filter);
        $label = LocalizationUtility::translateByKey('module.lead.list.overallajax', [$amount]);
        return $this->jsonResponse(json_encode(['amount' => $amount, 'amountLabel' => $label]));
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function getOverallCompaniesAjax(): ResponseInterface
    {
        $filter = BackendUtility::getFilterFromSession('companies', 'Lead');
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $amount = $companyRepository->findAmountByFilter($filter);
        $label = LocalizationUtility::translateByKey('module.lead.list.overallajax', [$amount]);
        return $this->jsonResponse(json_encode(['amount' => $amount, 'amountLabel' => $label]));
    }

    protected function addDocumentHeaderForCurrentController(): void
    {
        $actions = ['dashboard', 'list', 'companies'];
        $menuConfiguration = [];
        foreach ($actions as $action) {
            $menuConfiguration[] = [
                'action' => $action,
                'label' => LocalizationUtility::translate(
                    'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.lead.' . $action
                ),
            ];
        }
        $this->addDocumentHeader($menuConfiguration);
    }

    protected function isCompaniesViewDisabled(): bool
    {
        return ($this->settings['tracking']['company']['_enable'] ?? '') !== '1' ||
            ($this->settings['tracking']['company']['token'] ?? '') === '';
    }
}

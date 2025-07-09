<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\LiveSearch;

use In2code\Lux\Domain\Model\Visitor as VisitorModel;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Search\LiveSearch\ResultItem;
use TYPO3\CMS\Backend\Search\LiveSearch\ResultItemAction;
use TYPO3\CMS\Backend\Search\LiveSearch\SearchDemand\SearchDemand;
use TYPO3\CMS\Backend\Search\LiveSearch\SearchProviderInterface;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class Visitor implements SearchProviderInterface
{
    protected string $title = 'LUX Marketing Automation';
    protected int $limit = 10;
    protected array $results = [];

    public function __construct(
        protected readonly UriBuilder $uriBuilder,
        protected readonly VisitorRepository $visitorRepository,
        protected readonly IconFactory $iconFactory
    ) {
    }

    /**
     * @param SearchDemand $searchDemand
     * @return int
     * @throws InvalidQueryException
     */
    public function count(SearchDemand $searchDemand): int
    {
        if ($this->isEnabled() === false) {
            return 0;
        }
        return count($this->getResults($searchDemand->getQuery()));
    }

    /**
     * @param SearchDemand $searchDemand
     * @return array|ResultItem[]
     * @throws InvalidQueryException
     * @throws RouteNotFoundException
     */
    public function find(SearchDemand $searchDemand): array
    {
        $resultItems = [];
        if ($this->isEnabled()) {
            foreach ($this->getResults($searchDemand->getQuery()) as $visitor) {
                $resultItems[] = $this->getResultItem($visitor);
            }
        }
        return $resultItems;
    }

    public function getFilterLabel(): string
    {
        return $this->title;
    }

    /**
     * @param VisitorModel $visitor
     * @return ResultItem
     * @throws RouteNotFoundException
     */
    protected function getResultItem(VisitorModel $visitor): ResultItem
    {
        return (new ResultItem(self::class))
            ->setItemTitle($visitor->getFullNameWithEmail())
            ->setTypeLabel($this->title)
            ->setIcon($this->iconFactory->getIcon('extension-lux'))
            ->setActions(...$this->getActionsForResult($visitor));
    }

    /**
     * @param VisitorModel $visitor
     * @return array
     * @throws RouteNotFoundException
     */
    protected function getActionsForResult(VisitorModel $visitor): array
    {
        $actions = [];

        $uri = $this->uriBuilder->buildUriFromRoute('lux_LuxLead.Lead_detail', ['visitor' => $visitor->getUid()]);
        $action = (new ResultItemAction('open_website'))
            ->setLabel('Lead details')
            ->setIcon($this->iconFactory->getIcon('extension-lux'))
            ->setUrl($uri->__toString());
        $actions[] = $action;

        if ($visitor->getCompanyrecord() !== null) {
            $uri = $this->uriBuilder->buildUriFromRoute('lux_LuxLead.Lead_company', ['company' => $visitor->getCompanyrecord()->getUid()]);
            $action = (new ResultItemAction('open_website'))
                ->setLabel('Company details')
                ->setIcon($this->iconFactory->getIcon('extension-lux'))
                ->setUrl($uri->__toString());
            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * @param string $searchTerm
     * @return array
     * @throws InvalidQueryException
     */
    protected function getResults(string $searchTerm): array
    {
        if ($this->results === []) {
            $filter = ObjectUtility::getFilterDto()->setLimit($this->limit)->setSearchTerm($searchTerm);
            $this->results = $this->visitorRepository->findAllWithIdentifiedFirst($filter);
        }
        return $this->results;
    }

    /**
     * Check if backend user has access to the leads module
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        /** @var ModuleProvider $moduleProvider */
        $moduleProvider = GeneralUtility::makeInstance(ModuleProvider::class);
        return BackendUtility::getBackendUserAuthentication() !== null &&
            $moduleProvider->accessGranted('lux_LuxLead', BackendUtility::getBackendUserAuthentication());
    }
}

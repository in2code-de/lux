<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider\PageOverview;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * GotoutInternalDataProvider
 * to show 5 gotout visits to internal sources to a given page identifier
 */
class GotoutInternalDataProvider extends AbstractDataProvider
{
    /**
     * @var FilterDto|null
     */
    protected $filter = null;

    /**
     * @var PagevisitRepository|null
     */
    protected $pagevisitRepository = null;

    /**
     * @var PageRepository|null
     */
    protected $pageRepository = null;

    /**
     * Constructor
     *
     * @param FilterDto|null $filter contains timeframe and page identifier as searchterm
     */
    public function __construct(FilterDto $filter = null)
    {
        $this->filter = $filter;
        $this->pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     *  [
     *      123 => [
     *          'pageIdentifier' => 123,
     *          'title' => 'Home',
     *          'amount' => 135
     *      ]
     *  ]
     * @return array
     * @throws ExceptionDbal
     */
    public function get(): array
    {
        $visits = $this->getPagevisitsOfGivenPage();
        $results = [];
        foreach ($visits as $visit) {
            $page = $this->getGotoutToPagevisit($visit['visitor'], $visit['crdate']);
            if ($page > 0) {
                if (array_key_exists($page, $results) === false) {
                    $results[$page] = [
                        'pageIdentifier' => $page,
                        'title' => $this->pageRepository->findTitleByIdentifier($page),
                        'amount' => 1,
                    ];
                } else {
                    $results[$page]['amount']++;
                }
            }
        }

        usort($results, [$this, 'sortByAmount']);
        $results = array_slice($results, 0, $this->limit);
        $results = $this->cutResults($results);
        return $results;
    }

    /**
     * @param int $visitor
     * @param int $crdate
     * @return int
     * @throws ExceptionDbal
     */
    protected function getGotoutToPagevisit(int $visitor, int $crdate): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $pageIdentifier = $connection->executeQuery(
            'select page from ' . Pagevisit::TABLE_NAME
            . ' where visitor=' . (int)$visitor
            . ' and page != ' . (int)$this->filter->getSearchterm()
            . ' and crdate > ' . $crdate
            . ' and crdate <= ' . ($crdate + $this->getTimelimit())
            . ' order by crdate desc limit 1'
        )->fetchColumn();
        if ($pageIdentifier === false) {
            return 0;
        }
        return $pageIdentifier;
    }
}

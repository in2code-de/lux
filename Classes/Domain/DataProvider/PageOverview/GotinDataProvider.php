<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider\PageOverview;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * GotinDataProvider
 * to show 5 gotin visits to a given page identifier
 */
class GotinDataProvider
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
     * @throws Exception
     */
    public function __construct(FilterDto $filter = null)
    {
        $this->filter = $filter;
        $this->pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        $this->pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
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
        $result = [];
        foreach ($visits as $visit) {
            $page = $this->getGotinToPagevisit($visit['visitor'], $visit['crdate']);
            if ($page > 0) {
                if (array_key_exists($page, $result) === false) {
                    $result[$page] = [
                        'pageIdentifier' => $page,
                        'title' => $this->pageRepository->findTitleByIdentifier($page),
                        'amount' => 1
                    ];
                } else {
                    $result[$page]['amount']++;
                }
            }
        }

        usort($result, [$this, 'sortByAmount']);
        $result = array_slice($result, 0, 5);
        return $result;
    }

    /**
     * @param array $left
     * @param array $right
     * @return int
     */
    protected function sortByAmount(array $left, array $right): int
    {
        return ($left['amount'] < $right['amount']) ? 1 : (($left['amount'] > $right['amount']) ? -1 : 0);
    }

    /**
     * @param int $visitor
     * @param int $crdate
     * @return int
     * @throws ExceptionDbal
     */
    protected function getGotinToPagevisit(int $visitor, int $crdate): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $pageIdentifier = $connection->executeQuery(
            'select page from ' . Pagevisit::TABLE_NAME
            . ' where visitor=' . (int)$visitor
            . ' and page != ' . (int)$this->filter->getSearchterm()
            . ' and crdate < ' . $crdate
            . ' order by crdate desc limit 1'
        )->fetchColumn();
        if ($pageIdentifier === false) {
            return 0;
        }
        return $pageIdentifier;
    }

    /**
     * @return array
     * @throws ExceptionDbal
     */
    protected function getPagevisitsOfGivenPage(): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $records = $connection->executeQuery(
            'select uid,visitor,crdate from ' . Pagevisit::TABLE_NAME
            . ' where page=' . (int)$this->filter->getSearchterm()
            . ' and crdate > ' . $this->filter->getStartTimeForFilter()->format('U')
            . ' and crdate < ' . $this->filter->getEndTimeForFilter()->format('U')
        )->fetchAll();
        if ($records === false) {
            return [];
        }
        return $records;
    }
}

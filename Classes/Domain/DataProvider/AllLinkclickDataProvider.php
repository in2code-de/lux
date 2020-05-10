<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class AllLinkclickDataProvider
 */
class AllLinkclickDataProvider extends AbstractDataProvider
{
    /**
     * @var LinkclickRepository
     */
    protected $linkclickRepository = null;

    /**
     * LinkclickDataProvider constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        parent::__construct();
    }

    /**
     * Set values like
     *  [
     *      'titles' => [
     *          'Tagname Bar',
     *          'Tagname Foo'
     *      ],
     *      'amounts' => [ // linkclicks
     *          34,
     *          8
     *      ],
     *      'amounts2' => [ // pagevisitswithoutlinkclicks
     *          20,
     *          17,
     *      ],
     *      'performance' => [
     *          170,
     *          32
     *      ]
     *  ]
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    public function prepareData(): void
    {
        $data = $this->getGroupedData();
        $data = $this->sortGroupedDataByPerformanceAndLimitResult($data);
        $this->data = [
            'titles' => [],
            'amounts' => [],
            'amounts2' => [],
            'performance' => [],
        ];
        foreach ($data as $block) {
            $this->data['titles'][] = $block['title'];
            $this->data['amounts'][] = $block['linkclicks'];
            $this->data['amounts2'][] = $block['pagevisitswithoutlinkclicks'];
            $this->data['performance'][] = $block['performance'];
        }
    }

    /**
     * Sort previous data (and cut by a limit) like:
     *  [
     *      [
     *          'title' => 'Tagname Bar',
     *          'linkclicks' => 34,
     *          'pagevisits' => 54,
     *          'pagevisitswithoutlinkclicks' => 20,
     *          'performance' => 170
     *      ],
     *      [
     *          'title' => 'Tagname Foo',
     *          'linkclicks' => 8,
     *          'pagevisits' => 25,
     *          'pagevisitswithoutlinkclicks' => 17,
     *          'performance' => 32
     *      ]
     *  ]
     *
     * @param array $data
     * @param int $limit
     * @return array
     */
    public function sortGroupedDataByPerformanceAndLimitResult(array $data, int $limit = 5): array
    {
        usort($data, [$this, 'sortByPerformanceDescCallback']);
        $data = array_slice($data, 0, $limit);
        return $data;
    }

    /**
     * Group previous values by tag like:
     *  [
     *      [
     *          'title' => 'Tagname Foo',
     *          'linkclicks' => 8,
     *          'pagevisits' => 25,
     *          'pagevisitswithoutlinkclicks' => 17,
     *          'performance' => 32 // (linkclick / pagevisitswithoutlinkclicks * 100)
     *      ],
     *      [
     *          'title' => 'Tagname Bar',
     *          'linkclicks' => 34,
     *          'pagevisits' => 54,
     *          'pagevisitswithoutlinkclicks' => 20,
     *          'performance' => 170
     *      ]
     *  ]
     *
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function getGroupedData(): array
    {
        $ungroupedData = $this->getUngroupedData();
        $data = $data2 = [];

        foreach ($ungroupedData as $block) {
            unset($block['page']);
            if (array_key_exists($block['title'], $data) === false) {
                $data[$block['title']] = $block;
            } else {
                $data[$block['title']]['linkclicks'] += $block['linkclicks'];
                $data[$block['title']]['pagevisits'] += $block['pagevisits'];
                $data[$block['title']]['pagevisitswithoutlinkclicks'] += $block['pagevisitswithoutlinkclicks'];
            }
        }

        foreach ($data as $block) {
            $performance = 0;
            if ($block['linkclicks'] > 0 && $block['pagevisitswithoutlinkclicks'] > 0) {
                $performance = $block['linkclicks'] / $block['pagevisitswithoutlinkclicks'] * 100;
            }
            $data2[] = $block + ['performance' => (int)$performance];
        }

        return $data2;
    }

    /**
     * Get values grouped by pageUid like:
     *  [
     *      [
     *          'title' => 'Tagname Foo',
     *          'page' => 123,
     *          'linkclicks' => 5,
     *          'pagevisits' => 17,
     *          'pagevisitswithoutlinkclicks' => 12
     *      ],
     *      [
     *          'title' => 'Tagname Foo',
     *          'page' => 222,
     *          'linkclicks' => 3,
     *          'pagevisits' => 8,
     *          'pagevisitswithoutlinkclicks' => 5
     *      ],
     *      [
     *          'title' => 'Tagname Bar',
     *          'page' => 1,
     *          'linkclicks' => 34,
     *          'pagevisits' => 54,
     *          'pagevisitswithoutlinkclicks' => 20
     *      ]
     *  ]
     *
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    protected function getUngroupedData(): array
    {
        $linkclicks = $this->linkclickRepository->getAmountOfLinkclicksGroupedByPageUid($this->filter);
        $data = [];
        foreach ($linkclicks as $linkclick) {
            $pagevisits = $this->getPagevisitsFromPageByTagTimeframe($linkclick['page'], $linkclick['tag']);
            $pvWithoutLinkclicks = $pagevisits - $linkclick['count'];
            if ($pvWithoutLinkclicks < 0) {
                $pvWithoutLinkclicks = 0;
            }
            $data[] = [
                'title' => $linkclick['tag'],
                'page' => $linkclick['page'],
                'linkclicks' => $linkclick['count'],
                'pagevisits' => $pagevisits,
                'pagevisitswithoutlinkclicks' => $pvWithoutLinkclicks
            ];
        }
        return $data;
    }

    /**
     * Get the beginning of the day where the first linkclick was tracked as start and the day where
     * the latest linkclick was tracked (midnight) as end and check how many pagevisits exists for a pageUid
     *
     * @param int $pageUid
     * @param string $tag
     * @return int
     * @throws DBALException
     * @throws Exception
     * @throws \Exception
     */
    protected function getPagevisitsFromPageByTagTimeframe(int $pageUid, string $tag): int
    {
        $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        $start = DateUtility::convertTimestamp($this->linkclickRepository->getFirstCreationDateFromTagName($tag));
        $end = DateUtility::convertTimestamp($this->linkclickRepository->getLatestCreationDateFromTagName($tag));
        $start = DateUtility::getDayStart($start);
        $end = DateUtility::getDayEnd($end);
        $filter = ObjectUtility::getFilterDtoFromStartAndEnd($start, $end);
        return $pagevisitRepository->findAmountPerPage($pageUid, $filter);
    }

    /**
     * @param array $left
     * @param array $right
     * @return int
     */
    protected function sortByPerformanceDescCallback(array $left, array $right): int
    {
        return ($left['performance'] < $right['performance']) ? 1 : (($left['performance'] > $right['performance'])
            ? -1 : 0);
    }
}

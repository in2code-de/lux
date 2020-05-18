<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\LinkclickRepository;
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
     */
    public function prepareData(): void
    {
        $this->data = [
            'titles' => [],
            'amounts' => [],
            'amounts2' => [],
            'performance' => [],
        ];
    }
}

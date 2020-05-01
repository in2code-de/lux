<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class AbstractDataProvider to prepare data for diagrams and statistics in own or TYPO3 dashboards
 */
abstract class AbstractDataProvider implements DataProviderInterface, SingletonInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * AbstractDataProvider constructor.
     */
    public function __construct()
    {
        if ($this->data === []) {
            $this->prepareData();
        }
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}

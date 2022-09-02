<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\ObjectUtility;

/**
 * Class AbstractDataProvider to prepare data for diagrams and statistics in own or TYPO3 dashboards
 */
abstract class AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var FilterDto
     */
    protected $filter = null;

    /**
     * AbstractDataProvider constructor.
     * @param FilterDto|null $filter
     */
    public function __construct(FilterDto $filter = null)
    {
        if ($filter === null) {
            $this->filter = ObjectUtility::getFilterDto();
        } else {
            $this->filter = $filter;
        }
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

    /**
     * @return array
     */
    public function getTitlesFromData(): array
    {
        return (array)$this->getData()['titles'];
    }

    /**
     * @return string
     */
    public function getTitlesList(): string
    {
        return implode(',', $this->getTitlesFromData());
    }

    /**
     * @return array
     */
    public function getAmountsFromData(): array
    {
        $amouts = [];
        if (isset($this->getData()['amounts'])) {
            $amouts = $this->getData()['amounts'];
        }
        return (array)$amouts;
    }

    /**
     * @return string
     */
    public function getAmountsList(): string
    {
        return implode(',', $this->getAmountsFromData());
    }

    /**
     * @return array
     */
    public function getAmounts2FromData(): array
    {
        return (array)$this->getData()['amounts2'];
    }

    /**
     * @return string
     */
    public function getAmounts2List(): string
    {
        return implode(',', $this->getAmounts2FromData());
    }

    /**
     * @return int
     */
    public function getMaxY(): int
    {
        return (int)$this->getData()['max-y'];
    }

    /**
     * @return bool
     */
    public function isDataAvailable(): bool
    {
        return count($this->getAmountsFromData()) > 0;
    }
}

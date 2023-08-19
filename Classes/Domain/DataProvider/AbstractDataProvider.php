<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\ArrayUtility;
use In2code\Lux\Utility\ObjectUtility;

/**
 * Class AbstractDataProvider to prepare data for diagrams and statistics in own or TYPO3 dashboards
 */
abstract class AbstractDataProvider implements DataProviderInterface
{
    /**
     * Add a number bigger than 0 if you want to crop titles after those number of characters
     *
     * @var int
     */
    protected int $cropTitles = 0;
    protected array $data = [];

    protected ?FilterDto $filter = null;

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

    public function getData(): array
    {
        return $this->data;
    }

    public function getTitlesFromData(): array
    {
        $titles = (array)$this->getData()['titles'];
        if ($this->cropTitles > 0) {
            $titles = ArrayUtility::cropStringInArray($titles, $this->cropTitles);
        }
        return $titles;
    }

    public function getTitlesList(): string
    {
        return implode(',', $this->getTitlesFromData());
    }

    public function getAmountsFromData(): array
    {
        $amounts = [];
        if (isset($this->getData()['amounts'])) {
            $amounts = $this->getData()['amounts'];
        }
        return (array)$amounts;
    }

    public function getAmountsList(): string
    {
        return implode(',', $this->getAmountsFromData());
    }

    public function getAmounts2FromData(): array
    {
        return (array)($this->getData()['amounts2'] ?? []);
    }

    public function getAmounts2List(): string
    {
        return implode(',', $this->getAmounts2FromData());
    }

    public function getMaxY(): int
    {
        return (int)$this->getData()['max-y'];
    }

    public function isDataAvailable(): bool
    {
        return count($this->getAmountsFromData()) > 0;
    }
}

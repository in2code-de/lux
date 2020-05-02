<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

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
     * @throws Exception
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
        return $this->getData()['titles'];
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
        return $this->getData()['amounts'];
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
        return $this->getData()['amounts2'];
    }

    /**
     * @return string
     */
    public function getAmounts2List(): string
    {
        return implode(',', $this->getAmounts2FromData());
    }

    /**
     * @return bool
     */
    public function isDataAvailable(): bool
    {
        return count($this->getAmountsFromData()) > 0;
    }
}

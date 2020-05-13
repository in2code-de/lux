<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\StringUtility;

/**
 * Class AbstractDynamicFilterDataProvider
 */
abstract class AbstractDynamicFilterDataProvider extends AbstractDataProvider
{
    /**
     * Mapping to build a label from a date and a frequency from locallang
     *
     * @var array
     */
    protected $labelMapping = [
        'hour' => [
            'prefix' => 'datetime.n',
            'postfix' => ':00',
            'argumentDateFormat' => 'G'
        ],
        'day' => [
            'prefix' => 'datetime.weekday.',
            'postfixDateFormat' => 'D',
            'overruleLatest' => 'datetime.week.0'
        ],
        'week' => [
            'prefix' => 'datetime.week.n',
            'argumentDateFormat' => 'W',
        ],
        'month' => [
            'prefix' => 'datetime.month.',
            'postfixDateFormat' => 'n'
        ],
        'year' => [
            'prefix' => 'datetime.n',
            'argumentDateFormat' => 'Y'
        ],
    ];

    /**
     * @param string $frequency
     * @param \DateTime $dateTime
     * @return string
     */
    protected function getLabelForFrequency(string $frequency, \DateTime $dateTime): string
    {
        $arguments = null;
        $mapping = $this->labelMapping[$frequency];
        $key = $mapping['prefix'];
        if (!empty($mapping['postfixDateFormat'])) {
            $key .= strtolower($dateTime->format($mapping['postfixDateFormat']));
        }
        if (!empty($mapping['argumentDateFormat'])) {
            $arguments = [StringUtility::removeLeadingZeros($dateTime->format($mapping['argumentDateFormat']))];
        }
        $label = LocalizationUtility::translateByKey($key, $arguments);
        if (StringUtility::startsWith($label, 'Error:') && $arguments !== null) {
            $label = $arguments[0];
        }
        if (!empty($mapping['postfix'])) {
            $label .= $mapping['postfix'];
        }
        return $label;
    }

    /**
     * @param string $frequency
     * @return void
     */
    protected function overruleLatestTitle(string $frequency): void
    {
        $mapping = $this->labelMapping[$frequency];
        if (!empty($mapping['overruleLatest'])) {
            $key = array_key_last($this->data['titles']);
            $languageKey = $mapping['overruleLatest'];
            $this->data['titles'][$key] = LocalizationUtility::translateByKey($languageKey);
        }
    }
}

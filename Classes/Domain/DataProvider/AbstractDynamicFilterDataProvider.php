<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use DateTime;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\StringUtility;

abstract class AbstractDynamicFilterDataProvider extends AbstractDataProvider
{
    /**
     * Mapping to build a label from a date and a frequency from locallang
     *
     * @var array
     */
    protected array $labelMapping = [
        'hour' => [
            'prefix' => 'datetime.n',
            'postfix' => ':00',
            'argumentDateFormat' => 'G',
        ],
        'day' => [
            'prefix' => 'datetime.weekday.',
            'postfixDateFormat' => 'D',
            'overruleLatest' => 'datetime.week.0',
        ],
        'week' => [
            'prefix' => 'datetime.week.n',
            'argumentDateFormat' => 'W',
        ],
        'month' => [
            'prefix' => 'datetime.month.',
            'postfixDateFormat' => 'n',
        ],
        'year' => [
            'prefix' => 'datetime.n',
            'argumentDateFormat' => 'Y',
        ],
    ];

    protected function getLabelForFrequency(string $frequency, DateTime $dateTime): string
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

<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;

/**
 * Class DownloadsDataProvider
 */
class DownloadsDataProvider extends AbstractDataProvider
{
    /**
     * Mapping to build a label from a date and a frequency from locallang
     *
     * @var array
     */
    protected $labelMapping = [
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
     * Set values like:
     *  [
     *      'amounts' => [
     *          50,
     *          88,
     *          33
     *      ],
     *      'titles' => [
     *          'Th',
     *          'Fr',
     *          'Now'
     *      ]
     *  ]
     *
     * @return void
     * @throws \Exception
     */
    public function prepareData(): void
    {
        $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
        $intervals = $this->filter->getIntervals();
        $frequency = (string)$intervals['frequency'];
        foreach ($intervals['intervals'] as $interval) {
            $this->data['amounts'][] = $downloadRepository->getNumberOfDownloadsInTimeFrame(
                $interval['start'],
                $interval['end'],
                $this->filter
            );
            $this->data['titles'][] = $this->getLabelForFrequency($frequency, $interval['start']);
        }
        $this->overruleLatestTitle($frequency);
    }

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
        return LocalizationUtility::translateByKey($key, $arguments);
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

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use DateTime;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class VisitorScoringWeeksDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          11,
     *          32,
     *          88,
     *          120,
     *          22,
     *          18,
     *          20,
     *      ],
     *      'titles' => [
     *          '6 weeks ago',
     *          '5 weeks ago',
     *          '4 weeks ago',
     *          '3 weeks ago',
     *          '2 weeks ago',
     *          '1 week ago',
     *          'this week',
     *      ]
     *  ]
     *
     * @return void
     * @throws InvalidQueryException
     */
    public function prepareData(): void
    {
        $visitor = $this->filter->getVisitor();
        $amounts = [];
        if ($visitor !== null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $amounts = [
                $visitor->getScoring(),
                $visitor->getScoringByDate(new DateTime('7 days ago midnight')),
                $visitor->getScoringByDate(new DateTime('14 days ago midnight')),
                $visitor->getScoringByDate(new DateTime('21 days ago midnight')),
                $visitor->getScoringByDate(new DateTime('28 days ago midnight')),
                $visitor->getScoringByDate(new DateTime('35 days ago midnight')),
                $visitor->getScoringByDate(new DateTime('42 days ago midnight')),
            ];
            $amounts = array_reverse($amounts);
        }
        $this->data = ['amounts' => $amounts, 'titles' => $this->getTitles()];
    }

    public function getTitles(): array
    {
        $weekNames = [];
        foreach (range(0, 6) as $week) {
            $weekNames[] = LocalizationUtility::translateByKey('datetime.week.' . $week);
        }
        return array_reverse($weekNames);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ReferrerCategoryDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          120,
     *          88
     *      ],
     *      'titles' => [
     *          'socialMedia',
     *          'aiChats',
     *      ]
     *  ]
     *
     * @return void
     */
    public function prepareData(): void
    {
        /** @var PagevisitRepository $pagevisitRepository */
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $titles = $amounts = [];
        $counter = 0;
        foreach ($pagevisitRepository->getReferrerCategoryAmounts($this->filter) as $sourceKey => $amount) {
            $titles[] = LocalizationUtility::translateByKey('readablereferrer.' . $sourceKey);
            $amounts[] = $amount;
            if ($counter >= 5) {
                break;
            }
            $counter++;
        }
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}

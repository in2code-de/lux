<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Exception;
use In2code\Lux\Domain\Repository\CompanyRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CompanyAmountPerMonthDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          120,
     *          88,
     *          33,
     *      ],
     *      'titles' => [
     *          11, // November
     *          10, // October
     *          9, // September
     *      ]
     *  ]
     *
     * @return void
     * @throws Exception
     */
    public function prepareData(): void
    {
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $results = $companyRepository->findCompanyAmountOfLastSixMonths();
        $titles = $amounts = [];
        foreach ($results as $month => $amount) {
            $titles[] = LocalizationUtility::translateByKey('datetime.month.' . $month);
            $amounts[] = $amount;
        }
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\CompanyRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RevenueClassDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          120,
     *          88
     *      ],
     *      'titles' => [
     *          'ohne Angabe',
     *          '2.500.001 bis 5.000.000 Euro',
     *      ]
     *  ]
     *
     * @return void
     * @throws ExceptionDbal
     */
    public function prepareData(): void
    {
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $results = $companyRepository->findRevenueClasses($this->filter);
        $titles = $amounts = [];
        foreach ($results as $revenueClass => $amount) {
            $titles[] = LocalizationUtility::translateByKey('dictionary.revenue_class.' . $revenueClass);
            $amounts[] = $amount;
        }
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class ReferrerAmountDataProvider
 * to prepare data for referrer diagrams
 */
class ReferrerAmountDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          120,
     *          88
     *      ],
     *      'titles' => [
     *          'twitter.com',
     *          'facebook.com',
     *      ]
     *  ]
     *
     * @return void
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function prepareData(): void
    {
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $referrers = $pagevisitRepository->getAmountOfReferrers($this->filter);
        $titles = $amounts = [];
        $counter = 0;
        foreach ($referrers as $referrer => $amount) {
            $titles[] = $referrer;
            $amounts[] = $amount;
            if ($counter >= 5) {
                break;
            }
            $counter++;
        }
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}

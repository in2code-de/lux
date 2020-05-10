<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\ObjectUtility;
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
     */
    public function prepareData(): void
    {
        $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
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

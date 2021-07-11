<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class SocialMediaDataProvider
 */
class SocialMediaDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          120,
     *          88,
     *          60
     *      ],
     *      'titles' => [
     *          'Facebook',
     *          'LinkedIn',
     *          'Twitter'
     *      ]
     *  ]
     *
     * @return void
     * @throws Exception
     */
    public function prepareData(): void
    {
        $this->data = ['amounts' => [], 'titles' => []];
        $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        $result = $pagevisitRepository->getAmountOfSocialMediaReferrers($this->filter);
        foreach ($result as $title => $amount) {
            $this->data['amounts'][] = $amount;
            $this->data['titles'][] = $title;
        }
    }
}

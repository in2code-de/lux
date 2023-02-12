<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function prepareData(): void
    {
        $this->data = ['amounts' => [], 'titles' => []];
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $result = $pagevisitRepository->getAmountOfSocialMediaReferrers($this->filter);
        foreach ($result as $title => $amount) {
            $this->data['amounts'][] = $amount;
            $this->data['titles'][] = $title;
        }
    }
}

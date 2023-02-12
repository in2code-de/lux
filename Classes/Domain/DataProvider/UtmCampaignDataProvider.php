<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\UtmRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UtmCampaignDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          13,
     *          23,
     *      ],
     *      'titles' => [
     *          'Campaign A',
     *          'Campaign B',
     *      ]
     *  ]
     *
     * @return void
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public function prepareData(): void
    {
        $utmRepository = GeneralUtility::makeInstance(UtmRepository::class);
        $rows = $utmRepository->findCombinedByField('utm_campaign', $this->filter);
        foreach ($rows as $row) {
            $this->data['titles'][] = $row['utm_campaign'];
            $this->data['amounts'][] = $row['count'];
        }
    }
}

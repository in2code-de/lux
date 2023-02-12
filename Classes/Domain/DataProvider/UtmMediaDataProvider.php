<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\UtmRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UtmMediaDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          13,
     *          23,
     *          33,
     *      ],
     *      'titles' => [
     *          'email',
     *          'google',
     *          'app',
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
        $rows = $utmRepository->findCombinedByField('utm_medium', $this->filter);
        foreach ($rows as $row) {
            $this->data['titles'][] = $row['utm_medium'];
            $this->data['amounts'][] = $row['count'];
        }
    }
}

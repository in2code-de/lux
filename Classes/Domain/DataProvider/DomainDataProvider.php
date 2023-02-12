<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DomainDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          123,
     *          50,
     *          12
     *      ],
     *      'titles' => [
     *          'Page visits domain1.org',
     *          'Page visits domain2.org',
     *          'Page visits domain3.org'
     *      ]
     *  ]
     *
     * @return void
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function prepareData(): void
    {
        $languages = $this->getDomains();
        foreach ($languages as $language) {
            $this->data['amounts'][] = $language['count'];
            $this->data['titles'][] = $language['label'];
        }
    }

    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    protected function getDomains(): array
    {
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $rows = $pagevisitRepository->getDomainsWithAmountOfVisits($this->filter);

        foreach ($rows as &$row) {
            $row['label'] = LocalizationUtility::translateByKey('dataprovider.domain.label', [$row['domain']]);
        }
        return $rows;
    }
}

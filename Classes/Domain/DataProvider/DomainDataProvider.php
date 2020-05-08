<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\LocalizationUtility;

/**
 * Class DomainDataProvider
 */
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
     */
    protected function getDomains(): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $sql = 'SELECT count(*) as count, domain FROM ' . Pagevisit::TABLE_NAME
            . ' where domain!="" group by domain order by count desc';
        $rows = (array)$connection->executeQuery($sql)->fetchAll();

        foreach ($rows as &$row) {
            $row['label'] = LocalizationUtility::translateByKey('dataprovider.domain.label', [$row['domain']]);
        }
        return $rows;
    }
}

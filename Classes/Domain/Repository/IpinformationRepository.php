<?php

/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;

class IpinformationRepository extends AbstractRepository
{
    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Ipinformation::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Ipinformation::TABLE_NAME)->fetchOne();
    }

    /**
     * Get an array with number of visitors depending on the country where they came from
     *  Example return value:
     *  [
     *      'de' => 234,
     *      'at' => 45,
     *      'ch' => 11
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function findAllCountryCodesGrouped(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Ipinformation::TABLE_NAME);
        $rows = $connection->executeQuery(
            'select value from ' . Ipinformation::TABLE_NAME . ' i'
            . ' left join ' . Visitor::TABLE_NAME . ' v on i.visitor=v.uid'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor=v.uid'
            . ' where i.name = "countryCode"'
            . $this->extendWhereClauseWithFilterTime($filter, true, 'i')
            . $this->extendWhereClauseWithFilterTime($filter, true, 'pv')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
        )->fetchAllAssociative();

        $countryCodes = [];
        /** @var Ipinformation $ipinformation */
        foreach ($rows as $row) {
            $countryCode = $row['value'];
            if (!array_key_exists($countryCode, $countryCodes)) {
                $countryCodes[$countryCode] = 1;
            } else {
                $countryCodes[$countryCode]++;
            }
        }
        arsort($countryCodes);
        return $countryCodes;
    }
}

<?php
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class IpinformationRepository
 */
class IpinformationRepository extends AbstractRepository
{

    /**
     * Get an array with number of visitors depending to the country where they came from
     *  Example return value:
     *  [
     *      'de' => 234,
     *      'at' => 45,
     *      'ch' => 11
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     */
    public function findAllCountryCodesGrouped(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Ipinformation::TABLE_NAME);
        $rows = $connection->query(
            'select value from ' . Ipinformation::TABLE_NAME . ' where name = "countryCode"'
            . ' and crdate > ' . $filter->getStartTimeForFilter()->getTimestamp()
            . ' and tstamp < ' . $filter->getEndTimeForFilter()->getTimestamp() . ';'
        )->fetchAll();

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

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     */
    protected function extendLogicalAndWithFilterConstraints(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $logicalAnd[] = $query->greaterThan('crdate', $filter->getStartTimeForFilter());
        $logicalAnd[] = $query->lessThan('crdate', $filter->getEndTimeForFilter());
        return $logicalAnd;
    }
}

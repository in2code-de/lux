<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\DatabaseUtility;

/**
 * Class AnonymizeService to really anonymize and overwrite all privacy values (for local development or for a
 * presentation)
 */
class AnonymizeService
{
    /**
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function anonymizeAll()
    {
        $this->anonymizeIdentifiedVisitors();
        $this->anonymizeAttributes();
        $this->anonymizeIpinformation();
        $this->anonymizeAllFingerprints();
    }

    /**
     * @return void
     * @throws ExceptionDbal
     */
    protected function anonymizeIdentifiedVisitors()
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $sql = 'update ' . Visitor::TABLE_NAME
            . ' set email=' . $this->getRandomEmailClause()
            . ', ip_address="127.0.0.***"'
            . ', description=' . $this->getRandomStringClause(10, 'Random Description ')
            . ', company=' . $this->getRandomStringClause(10, 'Company ')
            . ', frontenduser=0'
            . ' where identified=1';
        $connection->executeQuery($sql);
    }

    /**
     * @return void
     * @throws ExceptionDbal
     */
    protected function anonymizeAttributes()
    {
        $connection = DatabaseUtility::getConnectionForTable(Attribute::TABLE_NAME);
        $statements = [
            'update ' . Attribute::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'Firstname ')
            . ' where name = "firstname" and value != \'\'',
            'update ' . Attribute::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'Lastname ')
            . ' where name = "lastname" and value != \'\'',
            'update ' . Attribute::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'Company ')
            . ' where name = "company" and value != \'\'',
            'update ' . Attribute::TABLE_NAME
            . ' set value=' . $this->getRandomEmailClause()
            . ' where name = "email" and value != \'\'',
            'update ' . Attribute::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'Random ')
            . ' where name not in ("firstname","lastname","company","email") and value != \'\'',
        ];
        foreach ($statements as $sql) {
            $connection->executeQuery($sql);
        }
    }

    /**
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function anonymizeIpinformation()
    {
        $connection = DatabaseUtility::getConnectionForTable(Ipinformation::TABLE_NAME);
        $statements = [
            'update ' . Ipinformation::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'Country ')
            . ' where name = "country"',
            'update ' . Ipinformation::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(2)
            . ' where name = "region"',
            'update ' . Ipinformation::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'City ')
            . ' where name = "city"',
            'update ' . Ipinformation::TABLE_NAME
            . ' set value=' . $this->getRandomNumberClause(5)
            . ' where name = "zip"',
            'update ' . Ipinformation::TABLE_NAME . ' set value=47.8479687 where name = "lat"',
            'update ' . Ipinformation::TABLE_NAME . ' set value=12.1110311 where name = "lon"',
            'update ' . Ipinformation::TABLE_NAME
            . ' set value=' . $this->getRandomStringClause(6, 'Company ')
            . ' where name in ("isp","org")',
        ];
        foreach ($statements as $sql) {
            $connection->executeQuery($sql);
        }
    }

    /**
     * @return void
     * @throws ExceptionDbal
     */
    protected function anonymizeAllFingerprints()
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Fingerprint::TABLE_NAME . ' set value=' . $this->getRandomStringClause(6, 'Fingerprint ') . ';'
        );
    }

    protected function getRandomStringClause(int $length, string $prefix = ''): string
    {
        return 'CONCAT(
            "' . $prefix . '",
            SUBSTR(MD5(FLOOR(RAND() * (100 - 1 + 1)) + 1), 1, ' . $length . ')
        )';
    }

    protected function getRandomEmailClause(): string
    {
        return 'CONCAT(
            SUBSTR(MD5(FLOOR(RAND() * (100 - 1 + 1)) + 1), 1, 10),
            "@mail.org"
        )';
    }

    /**
     * @param int $length
     * @param string $prefix
     * @return string
     * @throws ConfigurationException
     */
    protected function getRandomNumberClause(int $length, string $prefix = ''): string
    {
        if ($length < 1) {
            throw new ConfigurationException('length must be bigger then 0', 1682775629);
        }
        $maximum = 10 ** $length;
        $minimum = $maximum / 10;
        return 'CONCAT(
            "' . $prefix . '",
            FLOOR(RAND() * (' . $maximum . ' - ' . $minimum . ') + ' . $minimum . ')
        )';
    }
}

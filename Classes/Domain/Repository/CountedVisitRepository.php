<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Transfer\CountedVisitReference;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;

class CountedVisitRepository
{
    public const TABLES = [
        'tx_luxenterprise_domain_model_abpagevisit',
        'tx_luxenterprise_domain_model_shortenervisit',
    ];

    /**
     * @throws ExceptionDbal
     */
    public function assignVisitor(CountedVisitReference $reference, Visitor $visitor): bool
    {
        $assigned = false;
        if (in_array($reference->table, self::TABLES, true) && $visitor->getUid() > 0) {
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable($reference->table, true);
            $affectedRows = $queryBuilder
                ->update($reference->table)
                ->set('visitor', $visitor->getUid(), true, Connection::PARAM_INT)
                ->set('tstamp', time(), true, Connection::PARAM_INT)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($reference->uid, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'crdate',
                        $queryBuilder->createNamedParameter($reference->crdate, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq('visitor', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
                )
                ->executeStatement();
            $assigned = $affectedRows === 1;
        }
        return $assigned;
    }
}

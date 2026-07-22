<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\AbstractRepository;

/**
 * Exposes the protected filter where-clause builders of AbstractRepository for unit testing.
 *
 * The empty constructor bypasses the Extbase Repository constructor so the pure string-building
 * methods can be exercised without a database connection.
 */
class AbstractRepositoryFixture extends AbstractRepository
{
    public function __construct()
    {
    }

    public function callExtendWhereClauseWithFilterSizeClass(FilterDto $filter, string $table = ''): string
    {
        return $this->extendWhereClauseWithFilterSizeClass($filter, $table);
    }

    public function callExtendWhereClauseWithFilterRevenueClass(FilterDto $filter, string $table = ''): string
    {
        return $this->extendWhereClauseWithFilterRevenueClass($filter, $table);
    }
}

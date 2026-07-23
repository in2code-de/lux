<?php

namespace In2code\Lux\Tests\Functional\Fixtures\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\AbstractRepository;

/**
 * Exposes the protected where-clause builders of AbstractRepository. In contrast to the unit test fixture this does
 * NOT override quoteValue(), so the real driver based quoting is executed against the functional test database.
 */
class AbstractRepositoryAccessor extends AbstractRepository
{
    public function __construct()
    {
    }

    public function buildSizeClassClause(FilterDto $filter, string $table = ''): string
    {
        return $this->extendWhereClauseWithFilterSizeClass($filter, $table);
    }

    public function buildRevenueClassClause(FilterDto $filter, string $table = ''): string
    {
        return $this->extendWhereClauseWithFilterRevenueClass($filter, $table);
    }

    public function buildDomainClause(FilterDto $filter, string $table = ''): string
    {
        return $this->extendWhereClauseWithFilterDomain($filter, $table);
    }
}

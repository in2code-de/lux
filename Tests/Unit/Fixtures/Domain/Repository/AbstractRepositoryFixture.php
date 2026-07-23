<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\AbstractRepository;

/**
 * Exposes the protected where-clause builders of AbstractRepository so their SQL escaping can be unit tested.
 */
class AbstractRepositoryFixture extends AbstractRepository
{
    public function __construct()
    {
    }

    /**
     * Deterministic stand-in for the driver based quoting so the where-clause builders can be unit tested without a
     * database connection. It mirrors the escaping a MySQL/MariaDB driver applies (backslash and single quote).
     */
    protected function quoteValue(string $value): string
    {
        return '\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $value) . '\'';
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

    public function buildCountryClause(FilterDto $filter, string $table = ''): string
    {
        return $this->extendWhereClauseWithFilterCountry($filter, $table);
    }

    public function quoteList(array $values): string
    {
        return $this->quotedList($values);
    }
}

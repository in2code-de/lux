<?php

namespace In2code\Lux\Tests\Unit\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\AbstractRepository;
use In2code\Lux\Tests\Unit\Fixtures\Domain\Repository\AbstractRepositoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(AbstractRepository::class)]
#[CoversMethod(AbstractRepository::class, 'extendWhereClauseWithFilterSizeClass')]
#[CoversMethod(AbstractRepository::class, 'extendWhereClauseWithFilterRevenueClass')]
#[CoversMethod(AbstractRepository::class, 'extendWhereClauseWithFilterDomain')]
#[CoversMethod(AbstractRepository::class, 'extendWhereClauseWithFilterCountry')]
#[CoversMethod(AbstractRepository::class, 'quotedList')]
class AbstractRepositoryTest extends UnitTestCase
{
    protected AbstractRepositoryFixture $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AbstractRepositoryFixture();
    }

    public function testSizeClassValueIsQuoted(): void
    {
        $filter = new FilterDto();
        $filter->setSizeClass('05');
        self::assertSame(' and c.size_class = \'05\'', $this->repository->buildSizeClassClause($filter, 'c'));
    }

    /**
     * The breakout characters are removed by sanitizeString, the remaining keywords stay harmlessly inside the quotes
     * - this guards against a regression to raw, unquoted concatenation.
     */
    public function testSizeClassInjectionPayloadStaysInsideQuotes(): void
    {
        $filter = new FilterDto();
        $filter->setSizeClass('0" or 1=1');
        self::assertSame(' and c.size_class = \'0 or 11\'', $this->repository->buildSizeClassClause($filter, 'c'));
    }

    public function testRevenueClassValueIsQuoted(): void
    {
        $filter = new FilterDto();
        $filter->setRevenueClass('07');
        self::assertSame(' and c.revenue_class = \'07\'', $this->repository->buildRevenueClassClause($filter, 'c'));
    }

    public function testDomainValueIsQuoted(): void
    {
        $filter = new FilterDto();
        $filter->setDomain('in2code.de');
        self::assertSame(' and domain=\'in2code.de\'', $this->repository->buildDomainClause($filter));
    }

    public function testCountryValueIsQuoted(): void
    {
        $filter = new FilterDto();
        $filter->setCountry('DE');
        self::assertSame(' and country_code=\'DE\'', $this->repository->buildCountryClause($filter));
    }

    public function testValueListIsQuoted(): void
    {
        self::assertSame('\'lux\',\'second\',\'\'', $this->repository->quoteList(['lux', 'second', '']));
    }
}

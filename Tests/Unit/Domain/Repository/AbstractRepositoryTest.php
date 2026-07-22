<?php

namespace In2code\Lux\Tests\Unit\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\AbstractRepository;
use In2code\Lux\Tests\Unit\Fixtures\Domain\Repository\AbstractRepositoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Repository\AbstractRepository
 */
#[CoversClass(AbstractRepository::class)]
#[CoversMethod(AbstractRepository::class, 'extendWhereClauseWithFilterSizeClass')]
#[CoversMethod(AbstractRepository::class, 'extendWhereClauseWithFilterRevenueClass')]
class AbstractRepositoryTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected AbstractRepositoryFixture $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AbstractRepositoryFixture();
    }

    public static function sizeAndRevenueClassDataProvider(): array
    {
        return [
            'empty value produces no clause' => [
                '',
                '',
            ],
            'numeric class code is kept' => [
                '3',
                ' and c.size_class = 3',
            ],
            'zero padded class code is normalised to integer' => [
                '01',
                ' and c.size_class = 1',
            ],
            'union based sql injection collapses to integer' => [
                '0 union select 99999999,1 order by 1 desc',
                ' and c.size_class = 0',
            ],
            'boolean based sql injection collapses to integer' => [
                '1 or 1',
                ' and c.size_class = 1',
            ],
            'injection with denylist characters collapses to integer' => [
                '1) union select password from be_users -- ',
                ' and c.size_class = 1',
            ],
        ];
    }

    #[DataProvider('sizeAndRevenueClassDataProvider')]
    public function testExtendWhereClauseWithFilterSizeClassIsNotInjectable(
        string $sizeClass,
        string $expectedSql
    ): void {
        $filter = new FilterDto();
        $filter->setSizeClass($sizeClass);
        self::assertSame(
            $expectedSql,
            $this->repository->callExtendWhereClauseWithFilterSizeClass($filter, 'c')
        );
    }

    #[DataProvider('sizeAndRevenueClassDataProvider')]
    public function testExtendWhereClauseWithFilterRevenueClassIsNotInjectable(
        string $revenueClass,
        string $expectedSql
    ): void {
        $filter = new FilterDto();
        $filter->setRevenueClass($revenueClass);
        self::assertSame(
            str_replace('size_class', 'revenue_class', $expectedSql),
            $this->repository->callExtendWhereClauseWithFilterRevenueClass($filter, 'c')
        );
    }
}

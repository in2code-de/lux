<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Domain\Service\CountryService;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Service\CountryService
 */
class CountryServiceTest extends UnitTestCase
{
    /**
     * @var AccessibleObjectInterface|MockObject|CountryService
     * Todo: Add typehints to variable when PHP 7.4 is dropped
     */
    protected $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->generalValidatorMock = $this->getAccessibleMock(CountryService::class, null);
    }

    /**
     * @return void
     * @covers ::getCountryConfiguration
     */
    public function testGetCountryConfiguration(): void
    {
        self::assertSame(count($this->generalValidatorMock->getCountryConfiguration()), 248);
        self::assertSame(count($this->generalValidatorMock->getCountryConfiguration()['DE']), 3);
        self::assertSame($this->generalValidatorMock->getCountryConfiguration()['DE']['alpha2'], 'DE');
        self::assertSame($this->generalValidatorMock->getCountryConfiguration()['DE']['alpha3'], 'DEU');
        self::assertTrue(
            stristr($this->generalValidatorMock->getCountryConfiguration()['DE']['name'], 'Germany') !== false
        );
    }

    /**
     * @return void
     * @covers ::getPropertyByAlpha2
     */
    public function testGetPropertyByAlpha2(): void
    {
        self::assertSame($this->generalValidatorMock->getPropertyByAlpha2('de'), 'Germany');
        self::assertSame($this->generalValidatorMock->getPropertyByAlpha2('de', 'alpha2'), 'DE');
        self::assertSame($this->generalValidatorMock->getPropertyByAlpha2('de', 'alpha3'), 'DEU');
        self::assertSame($this->generalValidatorMock->getPropertyByAlpha2('xx'), '');
        self::assertSame($this->generalValidatorMock->getPropertyByAlpha2('xx', 'alpha3'), '');

        self::expectExceptionCode(1683376219);
        self::assertSame($this->generalValidatorMock->getPropertyByAlpha2('deu', 'alpha3'), 'DEU');
    }

    /**
     * @return void
     * @covers ::getAlpha2ByAnyProperty
     */
    public function testGetAlpha2ByAnyProperty(): void
    {
        self::assertSame($this->generalValidatorMock->getAlpha2ByAnyProperty('de'), 'DE');
        self::assertSame($this->generalValidatorMock->getAlpha2ByAnyProperty('deu'), 'DE');
        self::assertSame($this->generalValidatorMock->getAlpha2ByAnyProperty('Germany'), 'DE');
        self::assertSame($this->generalValidatorMock->getAlpha2ByAnyProperty('Germ'), 'DE');
        self::assertSame($this->generalValidatorMock->getAlpha2ByAnyProperty('xx'), '');
    }
}

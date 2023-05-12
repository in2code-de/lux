<?php

namespace In2code\Lux\Tests\Unit\Domain\Factory;

use In2code\Lux\Domain\Factory\UtmFactory;
use In2code\Lux\Domain\Model\Utm;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Factory\UtmFactory
 */
class UtmFactoryTest extends UnitTestCase
{
    /**
     * @var AccessibleObjectInterface|MockObject|UtmFactory
     * Todo: Add typehints to variable when PHP 7.4 is dropped
     */
    protected $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->generalValidatorMock = $this->getAccessibleMock(UtmFactory::class, null);
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    /**
     * @return void
     * @covers ::get
     */
    public function testGetUtmKeysEmptyArray(): void
    {
        $this->expectExceptionCode(1666207599);
        $this->generalValidatorMock->_call('get', [], '');
    }

    /**
     * @return void
     * @covers ::get
     */
    public function testGetUtmKeys(): void
    {
        $parameters = [
            'utm_source' => 'source',
            'utm_medium' => 'medium',
            'utm_campaign' => 'campaign',
            'utm_id' => '123',
            'utm_term' => 'term',
            'utm_content' => 'content',
        ];
        $utm = $this->generalValidatorMock->_call('get', $parameters, 'foo');
        self::assertInstanceOf(Utm::class, $utm);
        self::assertSame($parameters['utm_source'], $utm->getUtmSource());
        self::assertSame($parameters['utm_medium'], $utm->getUtmMedium());
        self::assertSame($parameters['utm_campaign'], $utm->getUtmCampaign());
        self::assertSame($parameters['utm_id'], $utm->getUtmId());
        self::assertSame($parameters['utm_term'], $utm->getUtmTerm());
        self::assertSame($parameters['utm_content'], $utm->getUtmContent());
        self::assertSame('foo', $utm->getReferrer());
    }

    /**
     * @return void
     * @covers ::getUtmKeys
     */
    public function testGet(): void
    {
        self::assertSame(
            $this->generalValidatorMock->_get('utmKeys'),
            $this->generalValidatorMock->_call('getUtmKeys')
        );
    }
}

<?php

namespace In2code\Lux\Tests\Unit\Domain\Factory;

use In2code\Lux\Domain\Factory\UtmFactory;
use In2code\Lux\Domain\Model\Utm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(UtmFactory::class)]
#[CoversMethod(UtmFactory::class, 'get')]
#[CoversMethod(UtmFactory::class, 'getAllUtmKeys')]
#[CoversMethod(UtmFactory::class, 'getUtmKeys')]
#[CoversMethod(UtmFactory::class, 'getUtmKeysForDatabase')]
class UtmFactoryTest extends UnitTestCase
{
    protected AccessibleObjectInterface|MockObject|UtmFactory $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->generalValidatorMock = $this->getAccessibleMock(UtmFactory::class, null);
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    public function testGetUtmKeysEmptyArray(): void
    {
        $this->expectExceptionCode(1666207599);
        $this->generalValidatorMock->_call('get', [], '');
    }

    public function testGet(): void
    {
        $parameters = [
            'utm_source' => 'source',
            'utm_medium' => 'medium',
            'utm_campaign' => 'campaign',
            'utm_id' => '1243',
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

    public function testGetUtmKeys(): void
    {
        self::assertSame(
            $this->generalValidatorMock->_get('utmKeys'),
            $this->generalValidatorMock->_call('getUtmKeys')
        );
    }

    public function testGetUtmKeysForDatabase(): void
    {
        self::assertSame(
            array_keys($this->generalValidatorMock->_get('utmKeys')),
            $this->generalValidatorMock->_call('getUtmKeysForDatabase')
        );
    }

    public function testGetAllUtmKeys(): void
    {
        $allKeys = [];
        foreach ($this->generalValidatorMock->_get('utmKeys') as $keys) {
            foreach ($keys as $key) {
                $allKeys[] = $key;
            }
        }
        self::assertSame($allKeys, $this->generalValidatorMock->_call('getAllUtmKeys'));
    }
}

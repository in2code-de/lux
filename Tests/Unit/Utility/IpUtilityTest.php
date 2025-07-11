<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\IpUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(IpUtility::class)]
#[CoversMethod(IpUtility::class, 'getIpAddressAnonymized')]
class IpUtilityTest extends UnitTestCase
{
    public static function getIpAddressAnonymizedDataProvider(): array
    {
        return [
            [
                '207.142.131.005',
                '207.142.131.***',
            ],
            [
                '207.142.131.5',
                '207.142.131.***',
            ],
            [
                '127.0.0.1',
                '127.0.0.***',
            ],
            [
                '2001:0db8:0000:08d3:0000:8a2e:0070:7344',
                '2001:0db8:0000:08d3:0000:8a2e:****:****',
            ],
            [
                '2001:0db8:0000:08d3:0000:8a2e:0070:734a',
                '2001:0db8:0000:08d3:0000:8a2e:****:****',
            ],
            [
                '2001:0db8::8d3::8a2e:7:7344',
                '2001:0db8::8d3::8a2e:****:****',
            ],
            [
                '::1',
                ':****:****',
            ],
        ];
    }

    #[DataProvider('getIpAddressAnonymizedDataProvider')]
    public function testGetIpAddressAnonymized(string $ipAddress, string $expectedResult): void
    {
        self::assertSame($expectedResult, IpUtility::getIpAddressAnonymized($ipAddress));
    }
}

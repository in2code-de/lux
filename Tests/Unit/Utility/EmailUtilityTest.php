<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\EmailUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass EmailUtility
 */
class EmailUtilityTest extends UnitTestCase
{
    public static function extendEmailReceiverArrayDataProvider(): array
    {
        return [
            [
                [
                    'alex@in2code.de',
                    'stefan@in2code.de',
                    'stefan.busemann@in2code.de',
                ],
                null,
                [
                    'alex@in2code.de' => 'receiver',
                    'stefan@in2code.de' => 'receiver',
                    'stefan.busemann@in2code.de' => 'receiver',
                ],
            ],
            [
                [
                    'foobar@test.de',
                    'foo@bar.co.uk',
                ],
                'name',
                [
                    'foobar@test.de' => 'name',
                    'foo@bar.co.uk' => 'name',
                ],
            ],
        ];
    }

    /**
     * @param array $emails
     * @param string|null $name
     * @param array $expectedResult
     * @return void
     * @dataProvider extendEmailReceiverArrayDataProvider
     * @covers ::extendEmailReceiverArray
     */
    public function testExtendEmailReceiverArray(array $emails, ?string $name, array $expectedResult): void
    {
        if ($name !== null) {
            $result = EmailUtility::extendEmailReceiverArray($emails, $name);
        } else {
            $result = EmailUtility::extendEmailReceiverArray($emails);
        }
        self::assertSame($expectedResult, $result);
    }

    /**
     * @return void
     * @covers ::getDomainFromEmail
     */
    public function testGetDomainFromEmail(): void
    {
        self::assertSame('in2code.de', EmailUtility::getDomainFromEmail('test@in2code.de'));
        self::assertSame('fuz.bayern', EmailUtility::getDomainFromEmail('foo.bar@fuz.bayern'));
        self::assertSame('', EmailUtility::getDomainFromEmail(''));
        self::assertSame('', EmailUtility::getDomainFromEmail('foobar'));
    }
}

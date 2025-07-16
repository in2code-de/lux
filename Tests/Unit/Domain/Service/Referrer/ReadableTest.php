<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Referrer\ReadableFixture;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Service\Referrer\Readable
 */
class ReadableTest extends UnitTestCase
{
    public static function getReadableReferrerDataProvider(): array
    {
        return [
            [
                'https://www.google.com', // Referrer URL
                'Google Organic', // Readable name
            ],
            [
                'https://t.co/anything/new',
                'X (Twitter)',
            ],
            [
                'https://syndicatedsearch.goog/abc12345',
                'Google AdSense',
            ],
            [
                'https://ads.microsoft.com/',
                'Microsoft Advertising',
            ],
            [
                'https://stackoverflow.com/questions/75556288/typo3-lux-newsletter',
                'Stack Overflow',
            ],
        ];
    }

    /**
     * @param string $referrer
     * @param string $expectedResult
     * @return void
     * @dataProvider getReadableReferrerDataProvider
     * @covers ::getReadableReferrer
     * @covers ::getDomain
     */
    public function testGetReadableReferrer(string $referrer, string $expectedResult): void
    {
        $readable = new ReadableFixture($referrer);
        self::assertSame($expectedResult, $readable->getReadableReferrer());
    }

    /**
     * @return void
     * @covers ::getOriginalReferrer
     */
    public function testGetOriginalReferrer(): void
    {
        $readable = new ReadableFixture('');
        self::assertGreaterThan($readable->getOriginalReferrer(), 10);
    }
}

<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Domain\Service\Referrer\Readable;
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
                'Google International', // Readable name
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
        $readable = new Readable($referrer);
        self::assertSame($readable->getReadableReferrer(), $expectedResult);
    }

    /**
     * @return void
     * @covers ::getOriginalReferrer
     */
    public function testGetOriginalReferrer(): void
    {
        $readable = new Readable('');
        self::assertGreaterThan($readable->getOriginalReferrer(), 10);
    }
}

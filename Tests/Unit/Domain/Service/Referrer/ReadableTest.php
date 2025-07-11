<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Referrer\ReadableFixture;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Service\Referrer\Readable
 */
class ReadableTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;
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

    public static function getKeyFromUrlDataProvider(): array
    {
        return [
            [
                'https://www.google.com',
                'searchEngines',
            ],
            [
                'https://t.co/anything/new',
                'socialMedia',
            ],
            [
                'https://chat.openai.com/path',
                'aiChats',
            ],
            [
                'https://www.linkedin.com/in/username',
                'socialMedia',
            ],
            [
                'https://unknown-domain.com',
                '',
            ],
        ];
    }

    /**
     * @param string $url
     * @param string $expectedResult
     * @return void
     * @dataProvider getKeyFromUrlDataProvider
     * @covers ::getKeyFromUrl
     */
    public function testGetKeyFromUrl(string $url, string $expectedResult): void
    {
        $readable = new ReadableFixture('');
        self::assertSame($expectedResult, $readable->getKeyFromUrl($url));
    }
    /**
     * @return void
     * @covers ::getDomainsFromCategory
     */
    public function testGetDomainsFromCategory(): void
    {
        $readable = new ReadableFixture('');

        // Test with existing category
        $domains = $readable->getDomainsFromCategory('socialMedia');
        self::assertIsArray($domains);
        self::assertNotEmpty($domains);
        self::assertContains('t.co', $domains);
        self::assertContains('www.facebook.com', $domains);

        // Test with non-existing category
        $emptyDomains = $readable->getDomainsFromCategory('nonExistingCategory');
        self::assertIsArray($emptyDomains);
        self::assertEmpty($emptyDomains);
    }

    /**
     * @return void
     * @covers ::getAllKeys
     */
    public function testGetAllKeys(): void
    {
        $readable = new ReadableFixture('');
        $keys = $readable->getAllKeys();

        self::assertIsArray($keys);
        self::assertNotEmpty($keys);
        self::assertArrayHasKey('socialMedia', $keys);
        self::assertArrayHasKey('searchEngines', $keys);

        // Check if "other" is the last key
        $lastKey = array_key_last($keys);
        self::assertEquals('other', $lastKey);
    }

    /**
     * @return void
     * @covers ::getReadableReferrer
     */
    public function testGetReadableReferrerWithUnknownDomain(): void
    {
        $unknownDomain = 'unknown-domain.com';
        $readable = new ReadableFixture('https://' . $unknownDomain);

        // When domain is not found, it should return the domain itself
        self::assertSame($unknownDomain, $readable->getReadableReferrer());
    }

    /**
     * @return void
     * @covers ::getKeyFromHost
     */
    public function testGetKeyFromHost(): void
    {
        $readable = new ReadableFixture('');

        // Test with existing host
        self::assertSame('searchEngines', $readable->getKeyFromHost('www.google.com'));
        self::assertSame('socialMedia', $readable->getKeyFromHost('www.facebook.com'));
        self::assertSame('aiChats', $readable->getKeyFromHost('chat.openai.com'));

        // Test with non-existing host
        self::assertSame('', $readable->getKeyFromHost('non-existing-domain.com'));
    }
}

<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Domain\Service\Referrer\SourceHelper;
use In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Referrer\SourceHelperFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(SourceHelper::class)]
#[CoversMethod(SourceHelper::class, 'getAllKeys')]
#[CoversMethod(SourceHelper::class, 'getDomainsFromCategory')]
#[CoversMethod(SourceHelper::class, 'getKeyFromHost')]
#[CoversMethod(SourceHelper::class, 'getKeyFromUrl')]
#[CoversMethod(SourceHelper::class, 'getOriginalReferrer')]
#[CoversMethod(SourceHelper::class, 'getReadableReferrer')]
class SourceHelperTest extends UnitTestCase
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
                'Google Ads',
            ],
            [
                'https://ads.microsoft.com/',
                'Microsoft Ads',
            ],
            [
                'https://stackoverflow.com/questions/75556288/typo3-lux-newsletter',
                'Stack Overflow',
            ],
        ];
    }

    #[DataProvider('getReadableReferrerDataProvider')]
    public function testGetReadableReferrer(string $referrer, string $expectedResult): void
    {
        $readable = new SourceHelperFixture($referrer);
        self::assertSame($expectedResult, $readable->getReadableReferrer());
    }

    public function testGetOriginalReferrer(): void
    {
        $readable = new SourceHelperFixture('');
        self::assertGreaterThan($readable->getOriginalReferrer(), 10);
    }

    public static function getKeyFromUrlDataProvider(): array
    {
        return [
            [
                'https://www.google.com',
                'search',
            ],
            [
                'https://t.co/anything/new',
                'social',
            ],
            [
                'https://chat.openai.com/path',
                'aiChats',
            ],
            [
                'https://www.linkedin.com/in/username',
                'social',
            ],
            [
                'https://unknown-domain.com',
                '',
            ],
        ];
    }

    #[DataProvider('getKeyFromUrlDataProvider')]
    public function testGetKeyFromUrl(string $url, string $expectedResult): void
    {
        $readable = new SourceHelperFixture('');
        self::assertSame($expectedResult, $readable->getKeyFromUrl($url));
    }

    public function testGetDomainsFromCategory(): void
    {
        $readable = new SourceHelperFixture('');

        // Test with the existing category
        $domains = $readable->getDomainsFromCategory('social');
        self::assertIsArray($domains);
        self::assertNotEmpty($domains);
        self::assertContains('t.co', $domains);
        self::assertContains('www.facebook.com', $domains);

        // Test with a non-existing category
        $emptyDomains = $readable->getDomainsFromCategory('nonExistingCategory');
        self::assertIsArray($emptyDomains);
        self::assertEmpty($emptyDomains);
    }

    public function testGetAllKeys(): void
    {
        $readable = new SourceHelperFixture('');
        $keys = $readable->getAllKeys(true);

        self::assertIsArray($keys);
        self::assertNotEmpty($keys);
        self::assertArrayHasKey('social', $keys);
        self::assertArrayHasKey('search', $keys);
        self::assertEquals('other', array_key_last($keys));

        $keys = $readable->getAllKeys();
        self::assertArrayHasKey('social', $keys);
        self::assertArrayHasKey('search', $keys);
        self::assertNotEquals('other', array_key_last($keys));
    }

    public function testGetReadableReferrerWithUnknownDomain(): void
    {
        $unknownDomain = 'unknown-domain.com';
        $readable = new SourceHelperFixture('https://' . $unknownDomain);

        // When a domain is not found, it should return the domain itself
        self::assertSame($unknownDomain, $readable->getReadableReferrer());
    }

    public function testGetKeyFromHost(): void
    {
        $readable = new SourceHelperFixture('');

        // Test with existing host
        self::assertSame('search', $readable->getKeyFromHost('www.google.com'));
        self::assertSame('social', $readable->getKeyFromHost('www.facebook.com'));
        self::assertSame('aiChats', $readable->getKeyFromHost('chat.openai.com'));

        // Test with non-existing host
        self::assertSame('', $readable->getKeyFromHost('non-existing-domain.com'));
    }
}

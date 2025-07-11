<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\UrlUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(UrlUtility::class)]
#[CoversMethod(UrlUtility::class, 'convertToRelative')]
#[CoversMethod(UrlUtility::class, 'isAbsoluteUri')]
#[CoversMethod(UrlUtility::class, 'getAttributeValueFromString')]
#[CoversMethod(UrlUtility::class, 'removeSlashPrefixAndPostfix')]
#[CoversMethod(UrlUtility::class, 'removeProtocolFromDomain')]
#[CoversMethod(UrlUtility::class, 'getHostFromUrl')]
class UrlUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public static function convertToRelativeDataProvider(): array
    {
        return [
            [
                '/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://domain.org',
            ],
            [
                'fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://domain.org',
            ],
            [
                'https://domain.org/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://domain.org',
            ],
            [
                '/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://webserver-selfhosting-support.localhost.de',
            ],
            [
                'fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://webserver-selfhosting-support.localhost.de',
            ],
            [
                'https://webserver-selfhosting-support.localhost.de/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://webserver-selfhosting-support.localhost.de',
            ],
        ];
    }

    #[DataProvider('convertToRelativeDataProvider')]
    public function testConvertToRelative(string $givenPath, string $expectedResult, string $domain): void
    {
        self::assertEquals($expectedResult, UrlUtility::convertToRelative($givenPath, $domain));
    }

    public function testIsAbsoluteUri(): void
    {
        self::assertTrue(UrlUtility::isAbsoluteUri('https://domain.org'));
        self::assertTrue(UrlUtility::isAbsoluteUri('http://domain.org/path/'));
        self::assertFalse(UrlUtility::isAbsoluteUri('/path/'));
        self::assertFalse(UrlUtility::isAbsoluteUri('path/'));
    }

    public function testGetAttributeValueFromString(): void
    {
        $tagString = '<tag data-anything-else="foo" data-anything="bar" class="test">';
        self::assertEquals('bar', UrlUtility::getAttributeValueFromString($tagString, 'data-anything'));
        self::assertEquals('test', UrlUtility::getAttributeValueFromString($tagString, 'class'));
    }

    public function testRemoveSlashPrefixAndPostfix(): void
    {
        self::assertEquals('path', UrlUtility::removeSlashPrefixAndPostfix('/path/'));
        self::assertEquals('path/folder', UrlUtility::removeSlashPrefixAndPostfix('/path/folder/'));
    }

    public static function removeProtocolFromDomainDataProvider(): array
    {
        return [
            [
                'https://domain.org',
                'domain.org',
            ],
            [
                'https://www.domain.org/path/file.ext',
                'www.domain.org/path/file.ext',
            ],
            [
                'http://domain.org',
                'domain.org',
            ],
        ];
    }

    #[DataProvider('removeProtocolFromDomainDataProvider')]
    public function testRemoveProtocolFromDomain(string $domain, string $expectedDomain): void
    {
        self::assertEquals($expectedDomain, UrlUtility::removeProtocolFromDomain($domain));
    }

    public function testGetHostFromUrl(): void
    {
        self::assertEquals('local.lux.de', UrlUtility::getHostFromUrl('https://local.lux.de/page/'));
        self::assertEquals('local.lux.de', UrlUtility::getHostFromUrl('https://local.lux.de'));
        self::assertEquals('local.lux.de', UrlUtility::getHostFromUrl('http://local.lux.de/page.html'));
    }
}

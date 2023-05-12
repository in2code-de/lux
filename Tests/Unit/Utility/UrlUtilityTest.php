<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\UrlUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Utility\UrlUtility
 */
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

    /**
     * @param string $givenPath
     * @param string $expectedResult
     * @param string $domain
     * @return void
     * @dataProvider convertToRelativeDataProvider
     * @covers ::convertToRelative
     */
    public function testConvertToRelative(string $givenPath, string $expectedResult, string $domain): void
    {
        self::assertEquals($expectedResult, UrlUtility::convertToRelative($givenPath, $domain));
    }

    /**
     * @return void
     * @covers ::isAbsoluteUri
     */
    public function testIsAbsoluteUri(): void
    {
        self::assertTrue(UrlUtility::isAbsoluteUri('https://domain.org'));
        self::assertTrue(UrlUtility::isAbsoluteUri('http://domain.org/path/'));
        self::assertFalse(UrlUtility::isAbsoluteUri('/path/'));
        self::assertFalse(UrlUtility::isAbsoluteUri('path/'));
    }

    /**
     * @return void
     * @covers ::getAttributeValueFromString
     */
    public function testGetAttributeValueFromString(): void
    {
        $tagString = '<tag data-anything-else="foo" data-anything="bar" class="test">';
        self::assertEquals('bar', UrlUtility::getAttributeValueFromString($tagString, 'data-anything'));
        self::assertEquals('test', UrlUtility::getAttributeValueFromString($tagString, 'class'));
    }

    /**
     * @return void
     * @covers ::removeSlashPrefixAndPostfix
     */
    public function testRemoveSlashPrefixAndPostfix(): void
    {
        self::assertEquals('path', UrlUtility::removeSlashPrefixAndPostfix('/path/'));
        self::assertEquals('path/folder', UrlUtility::removeSlashPrefixAndPostfix('/path/folder/'));
    }

    /**
     * @return array
     */
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

    /**
     * @param string $domain
     * @param string $expectedDomain
     * @return void
     * @dataProvider removeProtocolFromDomainDataProvider
     * @covers ::removeProtocolFromDomain
     */
    public function testRemoveProtocolFromDomain(string $domain, string $expectedDomain): void
    {
        self::assertEquals($expectedDomain, UrlUtility::removeProtocolFromDomain($domain));
    }
}

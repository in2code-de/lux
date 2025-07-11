<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\StringUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Utility\StringUtility
 */
#[CoversClass(StringUtility::class)]
#[CoversMethod(StringUtility::class, 'cleanString')]
#[CoversMethod(StringUtility::class, 'cropString')]
#[CoversMethod(StringUtility::class, 'getCurrentUri')]
#[CoversMethod(StringUtility::class, 'getDomainFromEmail')]
#[CoversMethod(StringUtility::class, 'getExtensionFromPathAndFilename')]
#[CoversMethod(StringUtility::class, 'getRandomString')]
#[CoversMethod(StringUtility::class, 'isJsonArray')]
#[CoversMethod(StringUtility::class, 'isShortMd5')]
#[CoversMethod(StringUtility::class, 'removeLeadingZeros')]
#[CoversMethod(StringUtility::class, 'removeStringPostfix')]
#[CoversMethod(StringUtility::class, 'removeStringPrefix')]
#[CoversMethod(StringUtility::class, 'shortMd5')]
#[CoversMethod(StringUtility::class, 'splitCamelcaseString')]
#[CoversMethod(StringUtility::class, 'startsWith')]
class StringUtilityTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function testGetExtensionFromPathAndFilename(): void
    {
        self::assertSame('jpg', StringUtility::getExtensionFromPathAndFilename('/var/www/filename.txt.jpg'));
        self::assertSame('txt', StringUtility::getExtensionFromPathAndFilename('fileadmin/path/text.txt'));
        self::assertSame('html', StringUtility::getExtensionFromPathAndFilename('file_abc_01.html'));
    }

    public function testStartsWith(): void
    {
        self::assertTrue(StringUtility::startsWith('abcdef', 'abc'));
        self::assertTrue(StringUtility::startsWith('012345', '0'));
        self::assertTrue(StringUtility::startsWith('../test', '../'));
        self::assertFalse(StringUtility::startsWith('../test', './'));
    }

    public function testGetCurrentUri(): void
    {
        self::assertTrue(stristr(StringUtility::getCurrentUri(), '://') !== false);
    }

    public function testIsJsonArray(): void
    {
        self::assertFalse(StringUtility::isJsonArray('abcdef'));
        self::assertFalse(StringUtility::isJsonArray('"string"'));
        self::assertTrue(StringUtility::isJsonArray('{"foo":"bar"}'));
        self::assertTrue(StringUtility::isJsonArray('{"foo":{"bar":"baz"}}'));
    }

    public function testCleanString(): void
    {
        self::assertSame('-_foo03Barbaz', StringUtility::cleanString('<>[]{}"\'#-_foo03Bar baz'));
        self::assertSame('-_foo03barbaz', StringUtility::cleanString('<>[]{}"\'#-_foo03Bar baz', true));
        self::assertSame('-_foo03bar baz', StringUtility::cleanString('<>[]{}"\'#-_foo03Bar baz', true, '_ -'));
    }

    public function testGetRandomString(): void
    {
        self::assertTrue(strlen(StringUtility::getRandomString()) === 32);
        self::assertTrue(strlen(StringUtility::getRandomString(64)) === 64);

        $randomString = StringUtility::getRandomString();
        $characters = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $found = false;
        foreach ($characters as $character) {
            if (stristr($randomString, $character)) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found);

        $randomString = StringUtility::getRandomString(100, false);
        $characters = range('A', 'Z');
        $found = false;
        foreach ($characters as $character) {
            if (str_contains($randomString, $character)) {
                $found = true;
                break;
            }
        }
        self::assertFalse($found);
    }

    public function testGetDomainFromEmail(): void
    {
        self::assertSame('in2code.de', StringUtility::getDomainFromEmail('a.k@in2code.de'));
        self::assertSame('typo3.co.uk', StringUtility::getDomainFromEmail('first_second.third@typo3.co.uk'));
    }

    public function testRemoveStringPrefix(): void
    {
        self::assertSame('test_02', StringUtility::removeStringPrefix('01_test_02', '01_'));
        self::assertSame('/test//test', StringUtility::removeStringPrefix('//test//test', '/'));
    }

    public function testRemoveStringPostfix(): void
    {
        self::assertSame('01_test', StringUtility::removeStringPostfix('01_test_02', '_02'));
        self::assertSame('//test//test/', StringUtility::removeStringPostfix('//test//test//', '/'));
    }

    public function testRemoveLeadingZeros(): void
    {
        self::assertSame('1_test_02', StringUtility::removeLeadingZeros('01_test_02'));
        self::assertSame('1234', StringUtility::removeLeadingZeros('01234'));
        self::assertSame('_01234', StringUtility::removeLeadingZeros('000_01234'));
        self::assertSame('1234', StringUtility::removeLeadingZeros('00001234'));
        self::assertSame('12034', StringUtility::removeLeadingZeros('12034'));
    }

    public function testCropString(): void
    {
        self::assertSame('lorem ipsum dolor...', StringUtility::cropString('lorem ipsum dolor met lorem ipsum'));
        self::assertSame('lorem', StringUtility::cropString('lorem ipsum dolor met lorem ipsum', 9, ''));
        self::assertSame('lorem ipsum', StringUtility::cropString('lorem ipsum dolor met lorem', 12, ''));
    }

    public function testShortMd5(): void
    {
        self::assertSame('acbd18', StringUtility::shortMd5('foo'));
        self::assertSame('acbd18db4cc2f85cedef654fccc4a4d8', StringUtility::shortMd5('foo', 32));
        self::assertSame('c4ca42', StringUtility::shortMd5('1'));
    }

    public function testIsShortMd5(): void
    {
        self::assertFalse(StringUtility::isShortMd5('foo'));
        self::assertFalse(StringUtility::isShortMd5('acbd18db4cc2f85cedef654fccc4a4d8'));
        self::assertFalse(StringUtility::isShortMd5('abc1234'));
        self::assertFalse(StringUtility::isShortMd5('abc12'));
        self::assertFalse(StringUtility::isShortMd5('~abcde'));
        self::assertFalse(StringUtility::isShortMd5('a8793cf9abc', 10));
        self::assertFalse(StringUtility::isShortMd5('a8793c 9abc', 10));
        self::assertTrue(StringUtility::isShortMd5('abc123'));
        self::assertTrue(StringUtility::isShortMd5('a8793cf9ab', 10));
        self::assertTrue(StringUtility::isShortMd5('acbd18db4cc2f85cedef654fccc4a4d8', 32));
    }

    public function testSplitCamelcaseString(): void
    {
        self::assertSame(['foo', 'Bar'], StringUtility::splitCamelcaseString('fooBar'));
        self::assertSame(
            ['Camel', 'Case', 'Test', 'Scenario'],
            StringUtility::splitCamelcaseString('CamelCaseTestScenario')
        );
        self::assertSame(['foo'], StringUtility::splitCamelcaseString('foo'));
    }
}

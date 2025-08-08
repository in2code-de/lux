<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\ArrayUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ArrayUtility::class)]
#[CoversMethod(ArrayUtility::class, 'cleanStringForArrayKeys')]
#[CoversMethod(ArrayUtility::class, 'convertFetchedAllArrayToNumericArray')]
#[CoversMethod(ArrayUtility::class, 'copyValuesToKeys')]
#[CoversMethod(ArrayUtility::class, 'sumAmountArrays')]
class ArrayUtilityTest extends UnitTestCase
{
    public static function sumAmountArraysDataProvider(): array
    {
        return [
            [
                [
                    'foo' => 3,
                    'bar' => 2,
                    'baz' => 8,
                ],
                [
                    'foo' => 1,
                    'baz' => 3,
                ],
                [
                    'foo' => 4,
                    'bar' => 2,
                    'baz' => 11,
                ],
            ],
            [
                [
                    '_' => 1,
                    '2' => 2,
                    'baz' => 3,
                ],
                [
                    '_' => 1,
                    '2' => 3,
                    'x' => 3,
                ],
                [
                    '_' => 2,
                    '2' => 5,
                    'baz' => 3,
                    'x' => 3,
                ],
            ],
            [
                [],
                [],
                [],
            ],
        ];
    }

    #[DataProvider('sumAmountArraysDataProvider')]
    public function testSumAmountArrays(array $array1, array $array2, array $expectedArray): void
    {
        self::assertSame($expectedArray, ArrayUtility::sumAmountArrays($array1, $array2));
    }

    public function testCleanStringForArrayKeys(): void
    {
        $arrayActual = [
            '<1>' => 'foo',
            'abc123' => 'bar',
            '1"test.5--' => 'baz',
        ];
        $arrayExpected = [
            '1' => 'foo',
            'abc123' => 'bar',
            '1test.5--' => 'baz',
        ];
        self::assertSame($arrayExpected, ArrayUtility::cleanStringForArrayKeys($arrayActual));
    }

    public function testCopyValuesToKeys(): void
    {
        $arrayActual = [
            'foo',
            'bar',
            'baz',
            '123test',
        ];
        $arrayExpected = [
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
            '123test' => '123test',
        ];
        self::assertSame($arrayExpected, ArrayUtility::copyValuesToKeys($arrayActual));
    }

    public static function convertFetchedAllArrayToNumericArrayDataProvider(): array
    {
        return [
            [
                [
                    [
                        'uid' => 123,
                        'pid' => 1,
                    ],
                    [
                        'uid' => 234,
                        'pid' => 1,
                    ],
                ],
                'uid',
                [123, 234],
            ],
            [
                [
                    [
                        'uid' => 123,
                        'pid' => 1,
                    ],
                    [
                        'uid' => 234,
                        'pid' => 2,
                    ],
                ],
                'pid',
                [1, 2],
            ],
            [
                [
                    [
                        'foo' => 123,
                    ],
                    [
                        'foo' => 234,
                    ],
                ],
                'uid',
                [],
            ],
        ];
    }

    #[DataProvider('convertFetchedAllArrayToNumericArrayDataProvider')]
    public function testConvertFetchedAllArrayToNumericArray(array $given, string $fieldName, array $expected): void
    {
        self::assertSame($expected, ArrayUtility::convertFetchedAllArrayToNumericArray($given, $fieldName));
    }
}

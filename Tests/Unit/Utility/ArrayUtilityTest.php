<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\ArrayUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Lux\Utility\ArrayUtility
 */
class ArrayUtilityTest extends UnitTestCase
{
    /**
     * @return array
     */
    public function sumAmountArraysDataProvider(): array
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

    /**
     * @return void
     * @dataProvider sumAmountArraysDataProvider
     * @covers ::sumAmountArrays
     */
    public function testSumAmountArrays(array $array1, array $array2, array $expectedArray)
    {
        self::assertSame($expectedArray, ArrayUtility::sumAmountArrays($array1, $array2));
    }

    /**
     * @return void
     * @covers ::cleanStringForArrayKeys
     */
    public function testCleanStringForArrayKeys()
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

    /**
     * @return void
     * @covers ::copyValuesToKeys
     */
    public function testCopyValuesToKeys()
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
}

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
}

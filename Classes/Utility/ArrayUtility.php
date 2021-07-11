<?php
declare(strict_types = 1);
namespace In2code\Lux\Utility;

/**
 * Class ArrayUtility
 */
class ArrayUtility
{
    /**
     * Example:
     *  [
     *      'foo' => 3,
     *      'bar' => 2,
     *      'baz' => 8
     *  ]
     *      +
     *  [
     *      'foo' => 5,
     *      'bar' => 1
     *  ]
     *      =
     *  [
     *      'foo' => 8,
     *      'bar' => 3,
     *      'baz' => 8
     *  ]
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function sumAmountArrays(array $array1, array $array2): array
    {
        $result = $array1;
        foreach (array_keys($array1) as $key1) {
            if (array_key_exists($key1, $array2)) {
                $result[$key1] += $array2[$key1];
            }
        }
        foreach ($array2 as $key2 => $amount2) {
            if (array_key_exists($key2, $array1) === false) {
                $result[$key2] = $amount2;
            }
        }
        return $result;
    }
}

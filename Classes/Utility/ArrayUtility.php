<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use In2code\Lux\Exception\ArgumentsException;
use In2code\Lux\Exception\ParametersException;

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

    public static function cleanStringForArrayKeys(array $array): array
    {
        $newArray = [];
        foreach (array_keys($array) as $key) {
            $newArray[StringUtility::cleanString($key, false, '._-')] = $array[$key];
        }
        return $newArray;
    }

    /**
     *  [
     *      0 => 'foo',
     *      1 => 'bar',
     *  ]
     *
     *  =>
     *
     *  [
     *      'foo' => 'foo',
     *      'bar' => 'bar',
     *  ]
     *
     * @param array $array
     * @return array
     */
    public static function copyValuesToKeys(array $array): array
    {
        $newArray = [];
        foreach ($array as $value) {
            $newArray[$value] = $value;
        }
        return $newArray;
    }

    public static function cropStringInArray(array $array, int $length = 20, string $append = '...'): array
    {
        foreach ($array as &$string) {
            if (is_string($string) === false) {
                throw new ParametersException('Only strings allowed in ' . __FUNCTION__, 1682685855);
            }
            $string = StringUtility::cropString($string, $length, $append);
        }
        return $array;
    }

    /**
     *  [
     *      [
     *          'uid' => 123,
     *      ],
     *      [
     *          'uid' => 234,
     *      ]
     *  ]
     *
     *  =>
     *
     *  [
     *      123,
     *      234,
     *  ]
     *
     * @param array $rows
     * @param string $fieldName
     * @return array
     * @throws ArgumentsException
     */
    public static function convertFetchedAllArrayToNumericArray(array $rows, string $fieldName = 'uid'): array
    {
        $new = [];
        foreach ($rows as $row) {
            if (is_array($row) === false) {
                throw new ArgumentsException('Given array must be 2-dimensional', 1687525394);
            }
            if (array_key_exists($fieldName, $row)) {
                $new[] = $row[$fieldName];
            }
        }
        return $new;
    }
}

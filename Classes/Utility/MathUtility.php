<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

/**
 * Class MathUtility
 */
class MathUtility
{
    /**
     * Get next 10 or 100 or 1000 and so on of a number
     *
     * @param int $number
     * @return int
     */
    public static function roundUp(int $number): int
    {
        for ($count = 10, $i = 0; $i < 10; $i++, $count *= 10) {
            if ($number < $count) {
                return $count;
            }
        }
        return 0;
    }
}

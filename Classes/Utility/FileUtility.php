<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

/**
 * Class FileUtility
 */
class FileUtility
{

    /**
     * @param string $pathAndFilename
     * @return string
     */
    public static function getFilenameFromPathAndFilename(string $pathAndFilename): string
    {
        $pathInfo = pathinfo($pathAndFilename);
        return $pathInfo['basename'];
    }

    /**
     * Search for a string in a file case-insensitive. Use linux grep command for best performance.
     *
     * @param string $value string to search for in file
     * @param string $filename absolute path and filename
     * @return bool
     */
    public static function isStringInFile(string $value, string $filename): bool
    {
        return exec('grep -i ' . escapeshellarg($value) . ' ' . $filename) !== '';
    }
}

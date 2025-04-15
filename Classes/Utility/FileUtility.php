<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

class FileUtility
{
    public static function getFilenameFromPathAndFilename(string $pathAndFilename): string
    {
        $pathInfo = pathinfo($pathAndFilename);
        return (string)$pathInfo['basename'];
    }

    public static function isImageFile(string $filename): bool
    {
        return in_array(self::getFileExtensionFromFilename($filename), ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Check for a string in a file (case-insensitive). Use linux grep command for best performance.
     *
     * @param string $value string to search for in file
     * @param string $filename absolute path and filename
     * @return bool
     */
    public static function isStringInFile(string $value, string $filename): bool
    {
        return self::searchForStringInFile($value, $filename) !== '';
    }

    /**
     * Search for a string in a file (case-insensitive). Use linux grep command for best performance.
     *
     * @param string $value string to search for in file
     * @param string $filename absolute path and filename
     * @return string
     */
    public static function searchForStringInFile(string $value, string $filename): string
    {
        return exec('grep -iw ' . escapeshellarg($value) . ' ' . $filename);
    }

    /**
     * Check for an exact string in a file (case-insensitive). Use linux grep command for best performance.
     *
     * @param string $value string to search for in file
     * @param string $filename absolute path and filename
     * @return bool
     */
    public static function isExactStringInFile(string $value, string $filename): bool
    {
        return self::searchForExactStringInFile($value, $filename) !== '';
    }

    /**
     * Search for a string in a file (case-insensitive). Use linux grep command for best performance.
     * While searchForStringInFile() search for any string in a line (as complete word), this function searches for
     * exact matches.
     *
     * @param string $value string to search for in file
     * @param string $filename absolute path and filename
     * @return string
     */
    protected static function searchForExactStringInFile(string $value, string $filename): string
    {
        return exec('grep -i \'^' . escapeshellarg($value) . '$\' ' . $filename);
    }

    public static function getFileExtensionFromFilename(string $filename): string
    {
        $info = pathinfo($filename);
        if (!empty($info['extension'])) {
            return $info['extension'];
        }
        return '';
    }
}

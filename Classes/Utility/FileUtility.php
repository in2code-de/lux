<?php
declare(strict_types = 1);
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
        return (string)$pathInfo['basename'];
    }

    /**
     * @param string $filename
     * @return bool
     */
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
     * @param string $filename
     * @return string
     */
    public static function getFileExtensionFromFilename(string $filename): string
    {
        $info = pathinfo($filename);
        if (!empty($info['extension'])) {
            return $info['extension'];
        }
        return '';
    }
}

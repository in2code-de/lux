<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

/**
 * Class UrlUtility
 */
class UrlUtility
{
    /**
     * Convert href links to relative links like
     *
     *  Example usage:
     *      "/fileadmin/file.pdf" => "fileadmin/file.pdf"
     *      "fileadmin/file.pdf" => "fileadmin/file.pdf"
     *      "https://domain.org/fileadmin/file.pdf" => "fileadmin/file.pdf"
     *
     * @param string $path
     * @param string $currentUri
     * @return string
     */
    public static function convertToRelative(string $path, string $currentUri = ''): string
    {
        if ($currentUri === '') {
            $currentUri = StringUtility::getCurrentUri();
        }
        $path = StringUtility::removeStringPrefix($path, $currentUri);
        $path = StringUtility::removeStringPrefix($path, '/');
        return $path;
    }
}

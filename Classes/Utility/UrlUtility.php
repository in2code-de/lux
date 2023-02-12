<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

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

    public static function isAbsoluteUri(string $uri): bool
    {
        return StringUtility::startsWith($uri, 'http://') || StringUtility::startsWith($uri, 'https://');
    }

    /**
     * data-anything="foo" => "foo"
     *
     * @param string $string
     * @param string $key
     * @return string
     */
    public static function getAttributeValueFromString(string $string, string $key): string
    {
        preg_match('~' . $key . '="([^"]*)"~', $string, $result);
        if (!empty($result[1])) {
            return $result[1];
        }
        return '';
    }

    public static function removeSlashPrefixAndPostfix(string $string): string
    {
        $string = ltrim($string, '/');
        $string = rtrim($string, '/');
        return $string;
    }

    public static function removeProtocolFromDomain(string $domain): string
    {
        return preg_replace('~https?://~', '', $domain);
    }
}

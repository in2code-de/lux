<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use In2code\Lux\Domain\Service\SiteService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public static function getHostFromUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        return $parsedUrl['host'] ?? '';
    }

    public static function isInternalUrl(string $url): bool
    {
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        foreach ($siteService->getAllDomains() as $domain) {
            if (UrlUtility::getHostFromUrl($url) === UrlUtility::removeSlashPrefixAndPostfix($domain)) {
                return true;
            }
        }
        return false;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class StringUtility
{
    public static function getExtensionFromPathAndFilename(string $pathAndFilename): string
    {
        $path = parse_url($pathAndFilename, PHP_URL_PATH);
        if (is_string($path)) {
            return pathinfo($path, PATHINFO_EXTENSION);
        }
        return '';
    }

    /**
     * Check if string starts with another string
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return stristr($haystack, $needle) && strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Get current scheme, domain and path of the current installation
     *
     * @return string
     */
    public static function getCurrentUri(): string
    {
        $uri = '';
        $uri .= parse_url(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'), PHP_URL_SCHEME);
        $uri .= '://' . GeneralUtility::getIndpEnv('HTTP_HOST') . '/';
        $uri .= rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'), '/');
        return $uri;
    }

    public static function isJsonArray(string $string): bool
    {
        return is_array(json_decode($string, true));
    }

    public static function cleanString(string $string, bool $toLower = false, string $addCharacters = '_-'): string
    {
        $expression = '~[^a-zA-Z0-9' . $addCharacters . ']~';
        $string = preg_replace($expression, '', $string);
        if ($toLower === true) {
            $string = strtolower($string);
        }
        return $string;
    }

    /**
     * Clean strings like GET or POST params for SQL usage or usage in HTML. Disallowed characters are removed.
     * Disallowed characters to sanitize SQL queries are: /\+*#?$%&!='"`´<>{}[]() and -- (double minus)
     *
     *  Example replacements:
     *      'Réne Nüßer' => 'Réne Nüßer',
     *      'Not this\=#?$&!;"\'´`<>{}[]()--nono' => 'Not thisnono',
     *      'But this@here.-_is,ok' => 'But this@here.-_is,ok',
     *
     * @param string $string
     * @return string
     */
    public static function sanitizeString(string $string): string
    {
        return preg_replace('/[\\#?$&!=\'"`´<>{}\[\]()]|--/', '', $string);
    }

    public static function getRandomString(int $length = 32, bool $lowerAndUpperCase = true): string
    {
        $characters = implode('', range(0, 9)) . implode('', range('a', 'z'));
        if ($lowerAndUpperCase) {
            $characters .= implode('', range('A', 'Z'));
        }
        $fileName = '';
        for ($i = 0; $i < $length; $i++) {
            $key = mt_rand(0, strlen($characters) - 1);
            $fileName .= $characters[$key];
        }
        return $fileName;
    }

    public static function getDomainFromEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (isset($parts[1])) {
            return $parts[1];
        }
        return '';
    }

    public static function removeStringPrefix(string $string, string $prefix): string
    {
        if (StringUtility::startsWith($string, $prefix)) {
            $string = substr($string, strlen($prefix));
        }
        return $string;
    }

    public static function removeStringPostfix(string $string, string $postfix): string
    {
        return preg_replace('~' . $postfix . '$~', '', $string);
    }

    /**
     * Remove leading zeros from a string but not if string is "0"
     *
     * @param string $string
     * @return string
     */
    public static function removeLeadingZeros(string $string): string
    {
        if ($string !== '0') {
            return ltrim($string, '0');
        }
        return $string;
    }

    public static function cropString(string $string, int $length = 20, string $append = '...'): string
    {
        $contentObject = ObjectUtility::getContentObject();
        return $contentObject->cropHTML($string, $length . '|' . $append . '|1');
    }

    public static function shortMd5(string $string, int $length = 6): string
    {
        return substr(md5($string), 0, $length);
    }

    public static function isShortMd5(string $string, int $length = 6): bool
    {
        return preg_replace('~[a-f0-9]{' . $length . '}~', '', $string) === '';
    }

    /**
     * Example result:
     *  "CamelCaseString" => ["Camel", "Case", "String"]
     *
     * @param string $string
     * @return array
     */
    public static function splitCamelcaseString(string $string): array
    {
        preg_match_all('~((?:^|[A-Z])[a-z]+)~', $string, $matches);
        return $matches[0];
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Utility;

/**
 * Class CookieUtility
 * is used to handle the merging of legacy cookie values (used as luxId in the past) and to identify per luxletter link
 */
class CookieUtility
{
    /**
     * @return string
     */
    public static function getLuxId(): string
    {
        return self::getCookieByName('luxId');
    }

    /**
     * @param string $name
     * @return string
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getCookieByName(string $name): string
    {
        if (!empty($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return '';
    }

    /**
     * Set a session cookie
     *
     * @param string $name
     * @param string $value
     * @return void
     * @noinspection PhpUnused Can be used by EXT:luxletter
     */
    public static function setCookie(string $name, string $value): void
    {
        setcookie($name, $value, 0, '/');
    }

    /**
     * @param string $name
     * @return void
     */
    public static function deleteCookie(string $name): void
    {
        setcookie($name, '', -1, '/');
    }
}

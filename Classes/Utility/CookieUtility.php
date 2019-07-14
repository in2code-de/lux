<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

/**
 * Class CookieUtility
 */
class CookieUtility
{

    /**
     * @return string
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getLuxId(): string
    {
        if (!empty($_COOKIE['luxId'])) {
            return $_COOKIE['luxId'];
        }
        return '';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function setLuxId(): string
    {
        $luxId = StringUtility::getRandomString(32, false);
        $expireDate = new \DateTime('+ 10 years');
        setcookie('luxId', $luxId, (int)$expireDate->format('U'), '/');
        return $luxId;
    }
}

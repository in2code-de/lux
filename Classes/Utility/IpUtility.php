<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

class IpUtility
{
    /**
     * Get visitors IP address. Also make a testing ip possible. And in addition get external IP if IP=="127.0.0.1" for
     * local testing environment.
     *
     * @param string $testIp for testing only
     * @return string
     */
    public static function getIpAddress(string $testIp = ''): string
    {
        $ipAddress = $testIp;
        if ($ipAddress === '') {
            $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            if ($ipAddress === '127.0.0.1') {
                $externalIpAddress = GeneralUtility::makeInstance(RequestFactory::class)
                    ->request('https://api.ipify.org/')
                    ->getBody()
                    ->getContents();
                if ($externalIpAddress === false) {
                    throw new UnexpectedValueException('Could not connect to https://api.ipify.org', 1518270238);
                }
                $ipAddress = $externalIpAddress;
            }
        }
        return $ipAddress;
    }

    public static function isCurrentIpInGivenRanges(array $ranges, string $testIp = ''): bool
    {
        $currentAddress = \IPLib\Factory::parseAddressString(self::getIpAddress($testIp));
        foreach ($ranges as $range) {
            $range = \IPLib\Factory::parseRangeString($range);
            if ($currentAddress->matches($range) === true) {
                return true;
            }
        }
        return false;
    }

    public static function getIpAddressAnonymized(string $testIp = ''): string
    {
        return preg_replace(
            ['/\.\d*$/', '/[\da-f]*:[\da-f]*$/'],
            ['.***', '****:****'],
            self::getIpAddress($testIp)
        );
    }
}

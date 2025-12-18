<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

class IpUtility
{
    /**
     * Get visitors IP address. Also make a testing ip possible. And in addition get external IP if IP is from
     * localhost or private network (Docker, local development) for local testing environment.
     *
     * @param string $testIp
     * @return string
     */
    public static function getIpAddress(string $testIp = ''): string
    {
        $ipAddress = $testIp;
        if (empty($ipAddress)) {
            $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');

            // Check for localhost or private IP ranges (Docker, local networks)
            if (self::isPrivateOrLocalIp($ipAddress)) {
                // Check for development IP override via environment variable
                $devIp = getenv('LUX_DEV_IP');
                if (!empty($devIp)) {
                    $ipAddress = $devIp;
                } else {
                    // Fetch external IP via API
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
        }
        return $ipAddress;
    }

    /**
     * Check if IP address is localhost or from private network range (RFC 1918).
     * This includes Docker container IPs and local development environments.
     *
     * Private ranges:
     * - 10.0.0.0/8 (10.0.0.0 - 10.255.255.255)
     * - 172.16.0.0/12 (172.16.0.0 - 172.31.255.255) - typical Docker range
     * - 192.168.0.0/16 (192.168.0.0 - 192.168.255.255)
     * - 127.0.0.0/8 (localhost)
     *
     * @param string $ip
     * @return bool
     */
    protected static function isPrivateOrLocalIp(string $ip): bool
    {
        // Check for localhost and private IP ranges (RFC 1918) using TYPO3's cmpIP utility
        return GeneralUtility::cmpIP($ip, '127.0.0.0/8, 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, ::1');
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

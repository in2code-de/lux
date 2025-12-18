<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use In2code\Lux\Exception\UnexpectedValueException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IpUtility
{
    /**
     * Get visitors IP address. Also make a testing ip possible. And in addition get external IP if IP is from
     * localhost or private network (Docker, local development) for local testing environment.
     *
     * @param string $testIp
     * @return string
     * @throws UnexpectedValueException
     */
    public static function getIpAddress(string $testIp = ''): string
    {
        $ipAddress = $testIp;
        if ($ipAddress === '') {
            $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            if (self::isPrivateOrLocalIp($ipAddress) && self::isTestingContext() === false) {
                $ipAddress = getenv('LUX_DEV_IP') ?: self::fetchExternalIp();
            }
        }
        return $ipAddress;
    }

    public static function getIpAddressAnonymized(string $testIp = ''): string
    {
        return preg_replace(
            ['/\.\d*$/', '/[\da-f]*:[\da-f]*$/'],
            ['.***', '****:****'],
            self::getIpAddress($testIp)
        );
    }

    /**
     * Fetch external IP address from public API services with fallback options for local dev environment only.
     * Tries multiple services for reliability.
     *
     * @return string
     * @throws UnexpectedValueException
     */
    protected static function fetchExternalIp(): string
    {
        $services = [
            'https://api.ipify.org',
            'https://icanhazip.com',
            'https://ifconfig.me/ip',
            'https://checkip.amazonaws.com',
        ];

        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);

        foreach ($services as $service) {
            try {
                $response = $requestFactory->request($service);
                $ip = trim($response->getBody()->getContents());
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            } catch (\Throwable $exception) {
                continue;
            }
        }

        throw new UnexpectedValueException('Could not fetch external IP from any service', 1766054740);
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

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function isTestingContext(): bool
    {
        return (bool)($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['lux']['testingContext'] ?? false);
    }
}

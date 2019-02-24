<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IpUtility
 */
class IpUtility
{

    /**
     * Get visitors IP address. Also make a testing ip possible. And in addition get external IP if IP=="127.0.0.1" for
     * local testing environment.
     *
     * @param string $testIp
     * @return string
     */
    public static function getIpAddress(string $testIp = ''): string
    {
        $ipAddress = $testIp;
        if (empty($ipAddress)) {
            $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            if ($ipAddress === '127.0.0.1') {
                $externalIpAddress = GeneralUtility::getUrl('https://api.ipify.org/');
                if ($externalIpAddress === false) {
                    throw new \UnexpectedValueException('Could not connect to https://api.ipify.org', 1518270238);
                }
                $ipAddress = $externalIpAddress;
            }
        }
        return $ipAddress;
    }
}

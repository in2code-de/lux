<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;

class EnvironmentUtility
{
    /**
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function isFrontend(): bool
    {
        if (isset($GLOBALS['TYPO3_REQUEST']) === false) {
            // E.g. when called from CLI
            return false;
        }
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function isBackend(): bool
    {
        if (isset($GLOBALS['TYPO3_REQUEST']) === false) {
            // E.g. when called from CLI
            return false;
        }
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }

    public static function isCli(): bool
    {
        return Environment::isCli();
    }
}

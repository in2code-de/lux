<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Http\ApplicationType;

class EnvironmentUtility
{
    /**
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function isFrontend(): bool
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function isBackend(): bool
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }
}

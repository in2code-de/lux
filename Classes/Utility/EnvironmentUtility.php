<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;

class EnvironmentUtility
{
    public static function isFrontend(): bool
    {
        if (ObjectUtility::getRequest() === null) {
            // E.g. when called from CLI
            return false;
        }
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    public static function isBackend(): bool
    {
        if (ObjectUtility::getRequest() === null) {
            // E.g. when called from CLI
            return false;
        }
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }

    public static function isCli(): bool
    {
        return Environment::isCli();
    }

    public static function isComposerMode(): bool
    {
        return defined('TYPO3_COMPOSER_MODE');
    }
}

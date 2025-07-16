<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use Throwable;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

class LocalizationUtility
{
    public static function translateByKey(string $key, ?array $arguments = null): ?string
    {
        $locallangPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        try {
            return self::translate($locallangPrefix . $key, 'Lux', $arguments);
        } catch (Throwable $exception) {
            // Use this part for unit testing
            return $key;
        }
    }

    public static function translate(string $key, string $extensionName = 'Lux', ?array $arguments = null): ?string
    {
        $label = LocalizationUtilityExtbase::translate($key, $extensionName, $arguments);
        if (empty($label)) {
            $label = $key;
        }
        return $label;
    }

    /**
     * @return LanguageService|null
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

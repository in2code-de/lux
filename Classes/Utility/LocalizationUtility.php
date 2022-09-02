<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

/**
 * Class LocalizationUtility
 */
class LocalizationUtility
{
    /**
     * @param string $key
     * @param array|null $arguments
     * @return string|null
     */
    public static function translateByKey(string $key, array $arguments = null)
    {
        $locallangPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        try {
            return self::translate($locallangPrefix . $key, 'Lux', $arguments);
        } catch (\Exception $exception) {
            // Use this part for unit testing
            return $key;
        }
    }

    /**
     * @param string $key
     * @param string $extensionName
     * @param array|null $arguments
     * @return string|null
     */
    public static function translate(string $key, string $extensionName = 'Lux', array $arguments = null)
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

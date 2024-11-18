<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ConfigurationUtility
{
    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getScoringCalculation(): string
    {
        $extensionConfig = self::getExtensionConfiguration();
        $scoringCalculation = $extensionConfig['scoringCalculation'] ?? '';
        if ($scoringCalculation === '') {
            $scoringCalculation
                = '(10 * numberOfSiteVisits) + (1 * numberOfPageVisits) + (20 * downloads) - (1 * lastVisitDaysAgo)';
        }
        return $scoringCalculation;
    }

    /**
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getCategoryScoringAddPageVisit(): int
    {
        $extensionConfig = self::getExtensionConfiguration();
        return (int)($extensionConfig['categoryScoringAddPageVisit'] ?? 10);
    }

    /**
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getCategoryScoringAddNewsVisit(): int
    {
        $extensionConfig = self::getExtensionConfiguration();
        return (int)($extensionConfig['categoryScoringAddNewsVisit'] ?? 10);
    }

    /**
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getCategoryScoringAddDownload(): int
    {
        $extensionConfig = self::getExtensionConfiguration();
        return (int)($extensionConfig['categoryScoringAddDownload'] ?? 20);
    }

    /**
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getCategoryScoringLinkListenerClick(): int
    {
        $extensionConfig = self::getExtensionConfiguration();
        return (int)($extensionConfig['categoryScoringLinkListenerClick'] ?? 20);
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isPageOverviewDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['disablePageOverview'] ?? '0') === '1';
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getPageOverviewView(): string
    {
        $extensionConfig = self::getExtensionConfiguration();
        $allowed = [
            'analysis',
            'leads',
        ];
        if (in_array(($extensionConfig['pageOverviewView'] ?? ''), $allowed)) {
            return $extensionConfig['pageOverviewView'];
        }
        return $allowed[0];
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isCkEditorConfigurationNeeded(): bool
    {
        return self::isCkEditorConfigurationDisabled() === false
            && ExtensionManagementUtility::isLoaded('rte_ckeditor');
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected static function isCkEditorConfigurationDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['disableCkEditorConfiguration'] ?? '0') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isIpLoggingDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['disableIpLogging'] ?? '0') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isAnonymizeIpEnabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['anonymizeIp'] ?? '1') === '1';
    }

    /**
     * @return string "all", "nosearchengine", "nogravatar", "noexternal"
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getLeadImageFromExternalSourcesConfiguration(): string
    {
        $extensionConfig = self::getExtensionConfiguration();
        if (array_key_exists('leadImageFromExternalSources', $extensionConfig)) {
            if ($extensionConfig['leadImageFromExternalSources'] === 'nogoogle') {
                $extensionConfig['leadImageFromExternalSources'] = 'nosearchengine';
            }
            return $extensionConfig['leadImageFromExternalSources'];
        }
        return 'all';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isShowRenderTimesEnabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['showRenderTimes'] ?? '0') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isUseCacheLayerEnabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['useCacheLayer'] ?? '1') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isLeadModuleDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['disableLeadModule'] ?? '0') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isAnalysisModuleDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['disableAnalysisModule'] ?? '0') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isExceptionLoggingActivated(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['enableExceptionLogging'] ?? '0') === '1';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isWorkflowModuleDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return ($extensionConfig['disableWorkflowModule'] ?? '0') === '1';
    }

    /**
     * Get extension configuration from LocalConfiguration.php
     *
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected static function getExtensionConfiguration(): array
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('lux');
    }

    /**
     * Todo: Can be removed if TYPO3 12 support is dropped
     *
     * @return bool
     */
    public static function isTypo3Version12(): bool
    {
        return self::isVersionToCompareSameOrHigherThenCurrentTypo3Version('12.4.99');
    }

    /**
     * @param string $versionToCompare like "1.2.3"
     * @return bool
     */
    public static function isVersionToCompareSameOrHigherThenCurrentTypo3Version(string $versionToCompare): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger($versionToCompare) >= self::getCurrentTypo3Version();
    }

    /**
     * Return current TYPO3 version as integer - e.g. 10003000 (10.3.0) or 9005014 (9.5.14)
     *
     * @return int
     */
    protected static function getCurrentTypo3Version(): int
    {
        return VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version());
    }
}

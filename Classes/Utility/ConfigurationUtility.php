<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class ConfigurationUtility
 */
class ConfigurationUtility
{

    /**
     * @return string
     */
    public static function getScoringCalculation(): string
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['scoringCalculation'];
    }

    /**
     * @return int
     */
    public static function getCategoryScoringAddPageVisit(): int
    {
        $extensionConfig = self::getExtensionConfiguration();
        return (int)$extensionConfig['categoryScoringAddPageVisit'];
    }

    /**
     * @return int
     */
    public static function getCategoryScoringAddDownload(): int
    {
        $extensionConfig = self::getExtensionConfiguration();
        return (int)$extensionConfig['categoryScoringAddDownload'];
    }

    /**
     * @return bool
     */
    public static function isLastLeadsBoxInPageDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['disableLastLeadsBoxInPage'] === '1';
    }

    /**
     * @return bool
     */
    public static function isIpLoggingDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['disableIpLogging'] === '1';
    }

    /**
     * @return bool
     */
    public static function isAnonymizeIpEnabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['anonymizeIp'] === '1';
    }

    /**
     * @return bool
     */
    public static function isIpInformationDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['disableIpInformation'] === '1';
    }

    /**
     * @return bool
     */
    public static function isLeadModuleDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['disableLeadModule'] === '1';
    }

    /**
     * @return bool
     */
    public static function isAnalysisModuleDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['disableAnalysisModule'] === '1';
    }

    /**
     * @return bool
     */
    public static function isWorkflowModuleDisabled(): bool
    {
        $extensionConfig = self::getExtensionConfiguration();
        return $extensionConfig['disableWorkflowModule'] === '1';
    }

    /**
     * @return bool
     */
    public static function isTypo3OlderThen9(): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9000000;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTypo3ConfigurationVariables(): array
    {
        return (array)$GLOBALS['TYPO3_CONF_VARS'];
    }

    /**
     * Get extension configuration from LocalConfiguration.php
     *
     * @return array
     */
    protected static function getExtensionConfiguration(): array
    {
        $configuration = [];
        if (ConfigurationUtility::isTypo3OlderThen9()) {
            $configVariables = self::getTypo3ConfigurationVariables();
            // @extensionScannerIgnoreLine We still need to access extConf for TYPO3 8.7
            $possibleConfig = unserialize((string)$configVariables['EXT']['extConf']['lux']);
            if (!empty($possibleConfig) && is_array($possibleConfig)) {
                $configuration = $possibleConfig;
            }
        } else {
            $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('lux');
        }
        return $configuration;
    }
}

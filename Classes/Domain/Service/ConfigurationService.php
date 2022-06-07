<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service;

use Throwable;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class ConfigurationService to get the TypoScript configuration from extension and cache it for multiple calls
 */
class ConfigurationService implements SingletonInterface
{
    const EXTENSION_NAME = 'Lux';

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @param string $path like "general.disallowedMailProviderList"
     * @param string $pluginName
     * @return mixed
     * @throws InvalidConfigurationTypeException
     */
    public function getTypoScriptSettingsByPath(string $path, string $pluginName = 'Fe')
    {
        $typoScript = $this->getTypoScriptSettings($pluginName);
        try {
            return ArrayUtility::getValueByPath($typoScript, $path, '.');
        } catch (Throwable $exception) {
            unset($exception);
        }
        return '';
    }

    /**
     * @param string $pluginName
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    public function getTypoScriptSettings(string $pluginName = 'Fe'): array
    {
        if (empty($this->settings[$pluginName])) {
            $this->settings[$pluginName] = $this->getTypoScriptSettingsFromOverallConfiguration($pluginName);
        }
        return $this->settings[$pluginName];
    }

    /**
     * @param string $pluginName
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    protected function getTypoScriptSettingsFromOverallConfiguration(string $pluginName): array
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        return (array)$configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            self::EXTENSION_NAME,
            $pluginName
        );
    }
}

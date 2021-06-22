<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

/**
 * Class ConfigurationService to get the typoscript configuration from extension and cache it for multiple calls
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
     */
    public function getTypoScriptSettingsByPath(string $path, string $pluginName = 'Fe')
    {
        $typoScript = $this->getTypoScriptSettings($pluginName);
        try {
            return ArrayUtility::getValueByPath($typoScript, $path, '.');
        } catch (\Exception $exception) {
            unset($exception);
        }
        return '';
    }

    /**
     * @param string $pluginName
     * @return array
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
     */
    protected function getTypoScriptSettingsFromOverallConfiguration(string $pluginName): array
    {
        $configurationManager = ObjectUtility::getObjectManager()->get(ConfigurationManagerInterface::class);
        return (array)$configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            self::EXTENSION_NAME,
            $pluginName
        );
    }
}

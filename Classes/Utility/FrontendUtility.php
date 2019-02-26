<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class FrontendUtility
 */
class FrontendUtility
{

    /**
     * @return int
     */
    public static function getCurrentPageIdentifier(): int
    {
        return (int)self::getTyposcriptFrontendController()->id;
    }

    /**
     * @return string
     */
    public static function getActionName(): string
    {
        $action = '';
        $plugin = self::getPluginName();
        $arguments = GeneralUtility::_GPmerged($plugin);
        if (!empty($arguments['action'])) {
            $action = $arguments['action'];
        }
        return $action;
    }

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        $module = '';
        $route = GeneralUtility::_GP('route');
        if (!empty($route)) {
            $module = rtrim(ltrim($route, '/lux/Lux'), '/');
        }
        if (ConfigurationUtility::isTypo3OlderThen9() === true) {
            $module = self::getModuleNameLegacy();
        }
        return $module;
    }

    /**
     * Get module name in TYPO3 8.7
     *
     * @return string
     */
    protected static function getModuleNameLegacy(): string
    {
        $module = '';
        $moduleName = GeneralUtility::_GP('M');
        if (!empty($moduleName)) {
            $module = ltrim($moduleName, 'lux_Lux');
        }
        return $module;
    }

    /**
     * @return string
     */
    public static function getPluginName(): string
    {
        $pluginName = 'tx_lux_lux_luxanalysis';
        if (!empty(GeneralUtility::_GPmerged('tx_lux_lux_luxworkflow'))) {
            $pluginName = 'tx_lux_lux_luxworkflow';
        }
        return $pluginName;
    }

    /**
     * @return TypoScriptFrontendController
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTyposcriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}

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
     * @return bool
     */
    public static function isLoggedInFrontendUser(): bool
    {
        return !empty(self::getTyposcriptFrontendController()->fe_user->user['uid']);
    }

    /**
     * @param string $propertyName
     * @return string
     */
    public static function getPropertyFromLoggedInFrontendUser($propertyName = 'uid'): string
    {
        $tsfe = self::getTyposcriptFrontendController();
        if (!empty($tsfe->fe_user->user[$propertyName])) {
            return (string)$tsfe->fe_user->user[$propertyName];
        }
        return '';
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
     * Get module name from GET param in backend context.
     * While TYPO3 9 delivers "/lux/LuxAnalysis/",
     * in 10 "/module/lux/LuxAnalysis" is delivered
     *
     * @return string - e.g. "Analysis"
     */
    public static function getModuleName(): string
    {
        $module = '';
        $route = GeneralUtility::_GP('route');
        if (!empty($route)) {
            if (ConfigurationUtility::isVersionToCompareSameOrLowerThenCurrentTypo3Version('10.0.0')) {
                // TYPO3 10.x
                $routParts = explode('/', $route);
                $module = ltrim(end($routParts), 'Lux');
            } else {
                // Todo: TYPO3 9.5.x
                $module = rtrim(ltrim($route, '/lux/Lux'), '/');
            }
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

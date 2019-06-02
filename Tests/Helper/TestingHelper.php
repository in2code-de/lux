<?php
namespace In2code\Lux\Tests\Helper;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class TestingHelper
 */
class TestingHelper
{

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function setDefaultConstants()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lux'] =
            'a:11:{s:13:"checkFunction";s:4:"User";s:18:"scoringCalculation";s:96:"' .
            '(10 * numberOfSiteVisits) + (1 * numberOfPageVisits) + (20 * downloads) - (1 * lastVisitDaysAgo)";' .
            's:27:"categoryScoringAddPageVisit";s:2:"10";s:26:"categoryScoringAddDownload";s:2:"20";s:21:' .
            '"disableAnalysisModule";s:1:"0";s:17:"disableLeadModule";s:1:"0";s:21:"disableWorkflowModule";' .
            's:1:"0";s:25:"disableLastLeadsBoxInPage";s:1:"0";s:16:"disableIpLogging";s:1:"0";s:11:"anonymizeIp"' .
            ';s:1:"1";s:20:"disableIpInformation";s:1:"0";}';
        $_SERVER['REMOTE_ADDR'] = '';
        $_SERVER['REQUEST_URI'] = '';
        $_SERVER['SSL_SESSION_ID'] = '';
        $_SERVER['ORIG_SCRIPT_NAME'] = '';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTPS'] = 'on';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['warning_email_addr'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['lockIP'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['BE']['sessionTimeout'] = 0;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['requestURIvar'] = false;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'error_log';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLogInit'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] = '';
        // @extensionScannerIgnoreLine
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_DLOG'] = false;
        if (!defined('TYPO3_OS')) {
            define('TYPO3_OS', 'LINUX');
        }
        if (!defined('PATH_site')) {
            define('PATH_site', self::getWebRoot());
        }
        if (!defined('PATH_thisScript')) {
            define('PATH_thisScript', self::getWebRoot() . 'typo3');
        }
        if (!defined('TYPO3_version')) {
            define('TYPO3_version', '8007000');
        }
        if (!defined('PHP_EXTENSIONS_DEFAULT')) {
            define('PHP_EXTENSIONS_DEFAULT', 'php');
        }
        if (!defined('FILE_DENY_PATTERN_DEFAULT')) {
            define('FILE_DENY_PATTERN_DEFAULT', '');
        }
    }

    /**
     * @param int|null pid
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function initializeTsfe($pid = null)
    {
        $configurationManager = new ConfigurationManager();
        $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();
        self::setDefaultConstants();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '.*';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects'] = [
            'TEXT' => 'TYPO3\CMS\Frontend\ContentObject\TextContentObject'
        ];
        $GLOBALS['TT'] = new TimeTracker();
        $GLOBALS['TSFE'] = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], $pid ?: 1, 0, true);
        $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'] = '1';
        $GLOBALS['TSFE']->fe_user->user['uid'] = 4784;
    }

    /**
     * @return string
     */
    public static function getWebRoot(): string
    {
        return realpath(__DIR__ . '/../../.Build/Web') . '/';
    }
}

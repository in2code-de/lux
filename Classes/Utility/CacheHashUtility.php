<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

class CacheHashUtility
{
    /**
     * Variables to be excluded from cHash checks
     *
     * @var array|string[]
     */
    protected static array $excludedVariables = [
        'tx_lux_fe[dispatchAction]',
        'tx_lux_fe[identificator]',
        'tx_lux_fe[arguments][pageUid]',
        'tx_lux_fe[arguments][languageUid]',
        'tx_lux_fe[arguments][referrer]',
        'tx_lux_fe[arguments][currentUrl]',
        'tx_lux_fe[arguments][newsUid]',
        'tx_lux_fe[arguments][abPageVisitIdentifier]',
        'tx_lux_fe[arguments][abTestingPage]',
        'tx_lux_fe[arguments][href]',
        'tx_lux_fe[arguments][linklistenerIdentifier]',
        'tx_lux_fe[arguments][redirectHash]',
        'tx_lux_fe[arguments][sendEmail]',
        'tx_lux_fe[arguments][values]',
        'tx_lux_fe[arguments][key]',
        'tx_lux_fe[arguments][value]',
        'tx_lux_fe[arguments][parameter]',
        'tx_lux_email4link[title]',
        'tx_lux_email4link[text]',
        'tx_lux_email4link[href]',
        'tx_lux_email4link[arguments][dummy]', // Placeholder as reminder for a dummy in email4link
        'tx_luxenterprise_cc[contentUid]',
        'tx_luxenterprise_cc[identificator]',
        'tx_luxenterprise_tr[typolink]',
        'tx_luxenterprise_api[arguments]',
    ];

    public static function addLuxArgumentsToExcludedVariables(): void
    {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = [];
        }
        $excludedParameters = &$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'];
        $excludedParameters = array_merge($excludedParameters, self::$excludedVariables);
    }
}

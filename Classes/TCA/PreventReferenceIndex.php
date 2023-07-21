<?php

declare(strict_types=1);
namespace In2code\Lux\TCA;

use TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent;

/**
 * Class PreventReferenceIndex
 * to prevent reference index records for any LUX tables, to keep database as small as needed
 */
class PreventReferenceIndex
{
    protected array $excludedTables = [
        'tx_lux_domain_model_visitor',
        'tx_lux_domain_model_fingerprint',
        'tx_lux_domain_model_attribute',
        'tx_lux_domain_model_pagevisit',
        'tx_lux_domain_model_newsvisit',
        'tx_lux_domain_model_download',
        'tx_lux_domain_model_ipinformation',
        'tx_lux_domain_model_search',
        'tx_lux_domain_model_linklistener',
        'tx_lux_domain_model_categoryscoring',
        'tx_lux_domain_model_linkclick',
        'tx_lux_redirect',
        'tx_lux_domain_model_utm',
        'tx_lux_domain_model_company',
        'tx_lux_domain_model_log',
        'tx_luxenterprise_domain_model_workflow',
        'tx_luxenterprise_domain_model_trigger',
        'tx_luxenterprise_domain_model_action',
        'tx_luxenterprise_domain_model_actionqueue',
        'tx_luxenterprise_domain_model_doubleoptin',
        'tx_luxenterprise_domain_model_shortener',
        'tx_luxenterprise_domain_model_shortenervisit',
        'tx_luxenterprise_abpage',
        'tx_luxenterprise_domain_model_abpagevisit',
        'tx_luxenterprise_domain_model_utmgenerator_uri',
        'tx_luxenterprise_domain_model_utmgenerator_campaign',
        'tx_luxenterprise_domain_model_utmgenerator_source',
        'tx_luxenterprise_domain_model_utmgenerator_medium',
    ];

    public function __invoke(IsTableExcludedFromReferenceIndexEvent $event)
    {
        if (in_array($event->getTable(), $this->excludedTables)) {
            $event->markAsExcluded();
        }
    }
}

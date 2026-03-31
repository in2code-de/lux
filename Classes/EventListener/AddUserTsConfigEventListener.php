<?php

declare(strict_types=1);
namespace In2code\Lux\EventListener;

use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\BeforeLoadedUserTsConfigEvent;

class AddUserTsConfigEventListener
{
    public function __invoke(BeforeLoadedUserTsConfigEvent $event): void
    {
        if (ConfigurationUtility::isAnalysisModuleDisabled()) {
            $event->addTsConfig('options.hideModules := addToList(lux_LuxAnalysis)');
        }
        if (ConfigurationUtility::isLeadModuleDisabled()) {
            $event->addTsConfig('options.hideModules := addToList(lux_LuxLead)');
        }
        if (ConfigurationUtility::isWorkflowModuleDisabled()) {
            $event->addTsConfig('options.hideModules := addToList(lux_LuxWorkflow)');
        }
    }
}

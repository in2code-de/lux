<?php

declare(strict_types=1);
namespace In2code\Lux\EventListener;

use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\BeforeLoadedPageTsConfigEvent;

class AddPageTsConfigEventListener
{
    public function __invoke(BeforeLoadedPageTsConfigEvent $event): void
    {
        if (ConfigurationUtility::isCkEditorConfigurationNeeded()) {
            $event->addTsConfig('RTE.default.preset = lux');
        }
    }
}

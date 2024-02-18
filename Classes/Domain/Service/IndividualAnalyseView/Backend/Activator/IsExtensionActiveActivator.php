<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class IsExtensionActiveActivator extends AbstractActivator
{
    public function isActive(): bool
    {
        $extensionKey = $this->getConfiguration()['extensionKey'] ?? '';
        return ExtensionManagementUtility::isLoaded($extensionKey);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Service\Uri\NewRecord;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class UriNewViewHelper
 */
class UriNewViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('tableName', 'string', 'tableName', true);
        $this->registerArgument('moduleName', 'string', 'module name for return url', true);
        $this->registerArgument('addReturnUrl', 'bool', 'addReturnUrl', false, true);
    }

    /**
     * @return string
     * @throws RouteNotFoundException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function render(): string
    {
        $newRecord = GeneralUtility::makeInstance(NewRecord::class, $this->arguments['moduleName']);
        return $newRecord->get(
            $this->arguments['tableName'],
            ConfigurationUtility::getPidLinkClickRedords(),
            (bool)$this->arguments['addReturnUrl']
        );
    }
}

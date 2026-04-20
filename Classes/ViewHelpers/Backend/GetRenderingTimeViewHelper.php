<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetRenderingTimeViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function render(): string
    {
        if (ConfigurationUtility::isShowRenderTimesEnabled() && BackendUtility::isAdministrator()) {
            $renderingTimeService = GeneralUtility::makeInstance(RenderingTimeService::class);
            $templateVariableContainer = $this->renderingContext->getVariableProvider();
            $templateVariableContainer->add('renderingTime', $renderingTimeService->getTime());
            $output = $this->renderChildren();
            $templateVariableContainer->remove('renderingTime');
            return $output;
        }
        return '';
    }
}

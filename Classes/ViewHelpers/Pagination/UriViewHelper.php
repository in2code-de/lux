<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Pagination;

use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class UriViewHelper extends AbstractTagBasedViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'identifier important if more widgets on same page', false, 'widget');
        $this->registerArgument('arguments', 'array', 'Arguments', false, []);
    }

    /**
     * Build an uri to current action with &tx_ext_plugin[currentPage]=2
     *
     * @return string The rendered uri
     */
    public function render(): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($this->renderingContext->getRequest());
        $uriBuilder->setAddQueryString(true);
        $extensionName = $this->renderingContext->getRequest()->getControllerExtensionName();
        $pluginName = $this->renderingContext->getRequest()->getPluginName();
        $extensionService = GeneralUtility::makeInstance(ExtensionService::class);
        $pluginNamespace = $extensionService->getPluginNamespace($extensionName, $pluginName);
        $argumentPrefix = $pluginNamespace . '[' . $this->arguments['name'] . ']';
        $arguments = $this->hasArgument('arguments') ? $this->arguments['arguments'] : [];
        if ($this->hasArgument('action')) {
            $arguments['action'] = $this->arguments['action'];
        }
        if ($this->hasArgument('format') && $this->arguments['format'] !== '') {
            $arguments['format'] = $this->arguments['format'];
        }

        return $uriBuilder->uriFor(
            $this->getAction(),
            [$argumentPrefix => $arguments],
            $this->getController()
        );
    }

    protected function getAction(): string
    {
        $controllerAction = $this->renderingContext->getControllerAction();
        $parts = explode('/', $controllerAction);
        return strtolower($parts[1] ?? '');
    }

    protected function getController(): string
    {
        $controllerAction = $this->renderingContext->getControllerAction();
        $parts = explode('/', $controllerAction);
        $controllerName = $parts[0] ?? '';
        if (ConfigurationUtility::isTypo3Version12()) {
            $controllerName = ucfirst($controllerName);
        }
        return $controllerName;
    }
}

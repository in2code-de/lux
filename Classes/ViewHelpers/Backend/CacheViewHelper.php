<?php

declare(strict_types=1);

namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CacheViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('cacheLayer', CacheLayer::class, 'cache layer class', true);
        $this->registerArgument('identifier', 'string', 'identifier for this cache', true);
    }

    /**
     * @return string
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function render(): string
    {
        /** @var CacheLayer $cacheLayer */
        $cacheLayer = $this->arguments['cacheLayer'];
        $identifier = $this->arguments['identifier'];

        if ($cacheLayer->isCacheAvailable($identifier)) {
            $content = $cacheLayer->getHtml($identifier);
            $content .= $this->getCacheContentPostfix();
        } else {
            $content = $this->renderChildren();
            $cacheLayer->setHtml($content, $identifier);
        }
        return $content;
    }

    protected function getCacheContentPostfix(): string
    {
        $content = PHP_EOL . '<!-- content from cache ';
        if (ConfigurationUtility::isShowRenderTimesEnabled() && BackendUtility::isAdministrator()) {
            $renderingTimeService = GeneralUtility::makeInstance(RenderingTimeService::class);
            $content .= $renderingTimeService->getTime() . 's';
        }
        $content .= '-->';
        return $content;
    }
}

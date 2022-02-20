<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Backend;

use Closure;
use In2code\Lux\Domain\Service\RenderingTimeService;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * GetRenderingTimeViewHelper
 */
class GetRenderingTimeViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Renders children only if showRenderTimes is enabled in global configuration.
     * Also add a new variable {renderingTime} to children elements.
     * If showRenderTimes is disabled, childrens are not rendered at all.
     *
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws UnexpectedValueException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if (ConfigurationUtility::isShowRenderTimesEnabled() && BackendUtility::isAdministrator()) {
            $renderingTimeService = GeneralUtility::makeInstance(RenderingTimeService::class);
            $templateVariableContainer = $renderingContext->getVariableProvider();
            $templateVariableContainer->add('renderingTime', $renderingTimeService->getTime());
            $output = $renderChildrenClosure();
            $templateVariableContainer->remove('renderingTime');
            return $output;
        }
        return '';
    }
}

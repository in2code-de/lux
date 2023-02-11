<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Uri;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

abstract class AbstractRecord implements RecordInterface
{
    protected RenderingContextInterface $renderingContext;

    public function __construct(RenderingContextInterface $renderingContext)
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Get return URL from current request
     *
     * @return string
     * @throws RouteNotFoundException
     */
    protected function getReturnUrl(): string
    {
        /** @var RenderingContext $renderingContext */
        $renderingContext = $this->renderingContext;
        $request = $renderingContext->getRequest();
        return $request->getAttribute('normalizedParams')->getRequestUri();
    }

    /**
     * @param string $route
     * @param array $parameters
     * @return string
     * @throws RouteNotFoundException
     */
    public static function getRoute(string $route, array $parameters = []): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute($route, $parameters);
    }
}

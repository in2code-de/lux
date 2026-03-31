<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Uri;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $request = method_exists($this->renderingContext, 'getRequest')
            ? $this->renderingContext->getRequest()
            : $this->renderingContext->getAttribute(ServerRequestInterface::class);
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

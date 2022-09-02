<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Uri;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractRecord
 */
abstract class AbstractRecord implements RecordInterface
{
    /**
     * @var string
     */
    protected $moduleName = '';

    /**
     * AbstractRecord constructor.
     * @param string $moduleName
     */
    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Get return URL from current request
     *
     * @return string
     * @throws RouteNotFoundException
     */
    protected function getReturnUrl(): string
    {
        return $this->getRoute($this->moduleName, $this->getCurrentParameters());
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

    /**
     * Get all GET/POST params without module name and token
     *
     * @param array $getParameters
     * @return array
     */
    public function getCurrentParameters(array $getParameters = []): array
    {
        if (empty($getParameters)) {
            $getParameters = GeneralUtility::_GET();
        }
        $parameters = [];
        $ignoreKeys = [
            'M',
            'moduleToken',
            'route',
            'token',
        ];
        foreach ($getParameters as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }
            $parameters[$key] = $value;
        }
        return $parameters;
    }
}

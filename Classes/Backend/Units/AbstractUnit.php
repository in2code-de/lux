<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Fluid\View\StandaloneView;

abstract class AbstractUnit
{
    protected array $arguments = [];
    protected ?FilterDto $filter = null;
    protected ?CacheLayer $cacheLayer = null;
    protected string $templateRootPath = 'EXT:lux/Resources/Private/Templates/Backend/Units';
    protected string $partialRootPath = 'EXT:lux/Resources/Private/Partials';
    protected string $classPrefix = 'In2code\Lux\Backend\Units\\';

    /**
     * Optional string for cacheLayer
     *
     * @var string
     */
    protected string $cacheLayerClass = '';

    /**
     * Optional string for cacheLayer
     *
     * @var string
     */
    protected string $cacheLayerFunction = '';

    /**
     * Optional string for filter from session
     *
     * @var string
     */
    protected string $filterClass = '';

    /**
     * Optional string for filter from session
     *
     * @var string
     */
    protected string $filterFunction = '';

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function get(): string
    {
        $this->initialize();
        return $this->getHtml();
    }

    protected function initialize(): void
    {
        $this->initializeFilter();
        $this->initializeCacheLayer();
    }

    protected function initializeFilter(): void
    {
        $filter = ObjectUtility::getFilterDto();
        if ($this->filterClass !== '' && $this->filterFunction !== '') {
            $filterArray = BackendUtility::getSessionValue('filter', $this->filterFunction, $this->filterClass);
            $propertyMapper = GeneralUtility::makeInstance(PropertyMapper::class);
            $filter = $propertyMapper->convert($filterArray, FilterDto::class);
        }
        $this->filter = $filter;
    }

    protected function initializeCacheLayer(): void
    {
        if ($this->cacheLayerClass !== '' && $this->cacheLayerFunction !== '') {
            $this->cacheLayer = GeneralUtility::makeInstance(CacheLayer::class);
            $this->cacheLayer->initialize($this->cacheLayerClass, $this->cacheLayerFunction);
        }
    }

    protected function getHtml(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths([$this->templateRootPath]);
        $view->setPartialRootPaths([$this->partialRootPath]);
        $view->setTemplate($this->getTemplatePath());
        $view->assignMultiple([
            'cacheLayerClass' => $this->cacheLayerClass,
            'cacheLayerFunction' => $this->cacheLayerFunction,
            'filterClass' => $this->filterClass,
            'filterFunction' => $this->filterFunction,
            'cacheLayer' => $this->cacheLayer,
            'filter' => $this->filter,
            'arguments' => $this->arguments,
        ] + $this->assignAdditionalVariables());
        return $view->render();
    }

    protected function assignAdditionalVariables(): array
    {
        return [];
    }

    protected function getArgument(string $key): string
    {
        if (array_key_exists($key, $this->arguments)) {
            return (string)$this->arguments[$key];
        }
        return '';
    }

    protected function getTemplatePath(): string
    {
        $path = StringUtility::removeStringPrefix(static::class, $this->classPrefix);
        return str_replace('\\', '/', $path);
    }
}

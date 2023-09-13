<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

abstract class AbstractUnit
{
    protected ?FilterDto $filter = null;
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

    public function get(): string
    {
        $this->initialize();
        return $this->getHtml();
    }

    protected function initialize(): void
    {
        $filter = ObjectUtility::getFilterDto();
        if ($this->filterClass !== '' && $this->filterFunction !== '') {
            $filterFromSession = BackendUtility::getSessionValue('filter', $this->filterFunction, $this->filterClass);
            if (is_a($filterFromSession, FilterDto::class)) {
                $filter = $filterFromSession;
            }
        }
        $this->filter = $filter;
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
            ] + $this->assignAdditionalVariables());
        $this->assignCacheLayer($view);
        $this->assignFilter($view);
        return $view->render();
    }

    protected function assignAdditionalVariables(): array
    {
        return [];
    }

    protected function assignCacheLayer(StandaloneView $view): void
    {
        if ($this->cacheLayerClass !== '' && $this->cacheLayerFunction !== '') {
            $cacheLayer = GeneralUtility::makeInstance(CacheLayer::class);
            $cacheLayer->initialize($this->cacheLayerClass, $this->cacheLayerFunction);
            $view->assign('cacheLayer', $cacheLayer);
        }
    }

    protected function assignFilter(StandaloneView $view): void
    {
        $view->assign('filter', $this->filter);
    }

    protected function getTemplatePath(): string
    {
        $path = StringUtility::removeStringPrefix(static::class, $this->classPrefix);
        return str_replace('\\', '/', $path);
    }
}

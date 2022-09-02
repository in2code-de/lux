<?php

declare(strict_types=1);

namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class CacheViewHelper
 */
class CacheViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
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
     */
    public function render(): string
    {
        /** @var CacheLayer $cacheLayer */
        $cacheLayer = $this->arguments['cacheLayer'];
        $identifier = $this->arguments['identifier'];

        if ($cacheLayer->isCacheAvailable($identifier)) {
            $content = $cacheLayer->getHtml($identifier);
        } else {
            $content = $this->renderChildren();
            $cacheLayer->setHtml($content, $identifier);
        }
        return $content;
    }
}

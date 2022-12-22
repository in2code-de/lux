<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ImplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'Any given array', false);
        $this->registerArgument('glue', 'string', 'Any glue character', false, ',');
    }

    public function render(): string
    {
        return implode($this->arguments['glue'], $this->getArray());
    }

    protected function getArray(): array
    {
        $array = $this->renderChildren();
        if (!empty($this->arguments['array'])) {
            $array = $this->arguments['array'];
        }
        return $array;
    }
}

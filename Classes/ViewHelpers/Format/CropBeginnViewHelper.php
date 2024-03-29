<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CropBeginnViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('length', 'int', 'Characters length', true);
        $this->registerArgument('prepend', 'string', 'Any prepend characters', false, '... ');
    }

    public function render(): string
    {
        $value = $this->renderChildren();
        $length = $this->arguments['length'] * -1;
        $string = substr($value, $length);
        if ($this->arguments['prepend'] !== '') {
            $string = $this->arguments['prepend'] . $string;
        }
        return $string;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\String;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetRangeViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('length', 'int', 'String length', false, 10);
        $this->registerArgument('start', 'int', 'String length', false, 0);
        $this->registerArgument('characterLength', 'int', 'Fill with leading zeros', false, false);
    }

    public function render(): array
    {
        $start = $this->arguments['start'];
        $length = $this->arguments['length'];
        $end = $start + $length;
        $result = [];
        for ($i = $start; $i < $end; $i++) {
            $value = $i;
            if ($this->arguments['characterLength']) {
                $value = str_pad((string)$value, $this->arguments['characterLength'], '0', STR_PAD_LEFT);
            }
            $result[] = $value;
        }
        return $result;
    }
}

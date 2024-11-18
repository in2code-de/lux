<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\String;

use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ImplodeOnObjectsPropertyViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('objects', 'mixed', 'Any object', true);
        $this->registerArgument('property', 'string', 'Any property of the object', true);
        $this->registerArgument('glue', 'string', 'glue character', false, ', ');
    }

    /**
     * @return string
     * @throws PropertyNotAccessibleException
     */
    public function render(): string
    {
        $properties = [];
        if (is_iterable($this->arguments['objects'])) {
            foreach ($this->arguments['objects'] as $object) {
                $properties[] = ObjectAccess::getProperty($object, $this->arguments['property']);
            }
        }
        return implode($this->arguments['glue'], $properties);
    }
}

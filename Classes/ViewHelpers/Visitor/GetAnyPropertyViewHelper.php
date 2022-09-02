<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Visitor;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetAnyPropertyViewHelper
 */
class GetAnyPropertyViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('property', 'string', 'any property name you want to get from the visitor', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        return (string)$visitor->getAnyPropertyByName($this->arguments['property']);
    }
}

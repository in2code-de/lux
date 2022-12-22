<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Visitor;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetCategoryScoringFromCategoryAndVisitorViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('category', Category::class, 'category', true);
    }

    public function render(): int
    {
        $scoring = 0;
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        /** @var Categoryscoring $categoryscoring */
        $categoryscoring = $visitor->getCategoryscoringByCategory($this->arguments['category']);
        if ($categoryscoring !== null) {
            $scoring = $categoryscoring->getScoring();
        }
        return $scoring;
    }
}

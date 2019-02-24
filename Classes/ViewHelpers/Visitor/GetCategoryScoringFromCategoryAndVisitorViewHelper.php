<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Visitor;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCategoryScoringFromCategoryAndVisitorViewHelper
 */
class GetCategoryScoringFromCategoryAndVisitorViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('category', Category::class, 'category', true);
    }

    /**
     * @return int
     */
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

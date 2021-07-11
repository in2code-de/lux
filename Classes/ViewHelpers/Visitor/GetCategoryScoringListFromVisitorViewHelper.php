<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Visitor;

use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetCategoryScoringListFromVisitorViewHelper
 */
class GetCategoryScoringListFromVisitorViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('property', 'string', 'Scoring or title from category(scoring)', false, 'scoring');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $list = [];
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        /** @var Categoryscoring $categoryscoring */
        foreach ($visitor->getCategoryscoringsSortedByScoring() as $categoryscoring) {
            $value = $categoryscoring->getScoring();
            if ($this->arguments['property'] === 'title' && $categoryscoring->getCategory() !== null) {
                $value = $categoryscoring->getCategory()->getTitle();
            }
            $list[] = $value;
        }
        return implode(',', $list);
    }
}

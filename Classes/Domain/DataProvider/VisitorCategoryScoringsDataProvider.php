<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class VisitorCategoryScoringsDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          11,
     *          32,
     *      ],
     *      'titles' => [
     *          'Product A',
     *          'Product B',
     *      ]
     *  ]
     *
     * @return void
     * @throws InvalidQueryException
     */
    public function prepareData(): void
    {
        $visitor = $this->filter->getVisitor();
        $amounts = $titles = [];
        if ($visitor !== null) {
            foreach ($visitor->getCategoryscoringsSortedByScoring() as $categoryscoring) {
                if ($categoryscoring !== null && $categoryscoring->getCategory() !== null) {
                    $amounts[] = $categoryscoring->getScoring();
                    $titles[] = $categoryscoring->getCategory()->getTitle();
                }
            }
        }
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class CompanyCategoryScoringsDataProvider extends AbstractDataProvider
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
        $visitors = $this->filter->getCompany()->getVisitors();
        $amounts = $titles = [];
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            foreach ($visitor->getCategoryscoringsSortedByScoring() as $categoryscoring) {
                if ($categoryscoring !== null && $categoryscoring->getCategory() !== null) {
                    if (isset($amounts[$categoryscoring->getCategory()->getUid()]) === false) {
                        $amounts[$categoryscoring->getCategory()->getUid()] = 0;
                    }
                    $amounts[$categoryscoring->getCategory()->getUid()] += $categoryscoring->getScoring();
                    $titles[$categoryscoring->getCategory()->getUid()] = $categoryscoring->getCategory()->getTitle();
                }
            }
        }
        array_multisort($amounts, SORT_DESC, $titles);
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}

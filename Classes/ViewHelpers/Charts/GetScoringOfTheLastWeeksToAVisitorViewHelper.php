<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Charts;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetScoringOfTheLastWeeksToAVisitorViewHelper
 */
class GetScoringOfTheLastWeeksToAVisitorViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('visitor', Visitor::class, 'Visitor', true);
    }

    /**
     * Get scorings of a visitor from the last 8 weeks
     *
     * @return string
     * @throws InvalidQueryException
     */
    public function render(): string
    {
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        /** @noinspection PhpUnhandledExceptionInspection */
        $scorings = [
            $visitor->getScoring(),
            $visitor->getScoringByDate(new \DateTime('7 days ago midnight')),
            $visitor->getScoringByDate(new \DateTime('14 days ago midnight')),
            $visitor->getScoringByDate(new \DateTime('21 days ago midnight')),
            $visitor->getScoringByDate(new \DateTime('28 days ago midnight')),
            $visitor->getScoringByDate(new \DateTime('35 days ago midnight')),
            $visitor->getScoringByDate(new \DateTime('42 days ago midnight')),
        ];
        $scorings = array_reverse($scorings);
        return implode(',', $scorings);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Page;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetLastVisitedPageByPageIdentifierViewHelper
 */
class GetLastVisitedPageByPageIdentifierViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('pageIdentifier', 'int', 'page identifier', false);
    }

    /**
     * Get last pagevisit from visitor (if pageIdentifier is not given, return very first. If pageIdentifier ist given,
     * return the first that fits to this identifier)
     *
     * @return Pagevisit|null
     * @throws \Exception
     */
    public function render()
    {
        $thisPagevisit = null;
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        /** @var Pagevisit $pagevisit */
        foreach ($visitor->getPagevisits() as $pagevisit) {
            if ($pagevisit->getPage() !== null) {
                if ($this->arguments['pageIdentifier'] === null
                    || (int)$this->arguments['pageIdentifier'] === $pagevisit->getPage()->getUid()) {
                    $thisPagevisit = $pagevisit;
                    break;
                }
            }
        }
        return $thisPagevisit;
    }
}

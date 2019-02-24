<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Page;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetVisitedPageByPageIdentifierViewHelper
 */
class GetVisitedPageByPageIdentifierViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('pageIdentifier', 'int', 'page identifier', true);
    }

    /**
     * Get all pagevisits from visitor related to a given page identifier
     *
     * @return array
     */
    public function render(): array
    {
        $pagevisits = [];
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        /** @var Pagevisit $pagevisit */
        foreach ($visitor->getPagevisits() as $pagevisit) {
            if ($pagevisit->getPage() !== null) {
                if ((int)$this->arguments['pageIdentifier'] === $pagevisit->getPage()->getUid()) {
                    $pagevisits[] = $pagevisit;
                }
            }
        }
        return $pagevisits;
    }
}

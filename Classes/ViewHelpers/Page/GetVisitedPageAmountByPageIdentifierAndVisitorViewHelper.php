<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Page;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetVisitedPageAmountByPageIdentifierAndVisitorViewHelper extends AbstractViewHelper
{
    protected PagevisitRepository $pagevisitRepository;

    public function __construct(PagevisitRepository $pagevisitRepository)
    {
        $this->pagevisitRepository = $pagevisitRepository;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
        $this->registerArgument('pageIdentifier', 'int', 'page identifier', true);
    }

    /**
     * @return int
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function render(): int
    {
        return $this->pagevisitRepository->findAmountPerPageAndVisitor(
            (int)$this->arguments['pageIdentifier'],
            $this->arguments['visitor']
        );
    }
}

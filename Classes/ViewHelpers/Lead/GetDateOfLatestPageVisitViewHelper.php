<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Lead;

use DateTime;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetDateOfLatestPageVisitViewHelper extends AbstractViewHelper
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
    }

    public function render(): ?DateTime
    {
        return $this->pagevisitRepository->findLatestDateByVisitor($this->arguments['visitor']);
    }
}

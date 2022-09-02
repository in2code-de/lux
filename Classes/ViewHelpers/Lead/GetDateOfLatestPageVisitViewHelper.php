<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Lead;

use Doctrine\DBAL\Exception;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetDateOfLatestPageVisitViewHelper
 */
class GetDateOfLatestPageVisitViewHelper extends AbstractViewHelper
{
    /**
     * @var PagevisitRepository|null
     */
    protected $pagevisitRepository = null;

    /**
     * GetDateOfLatestPageVisitViewHelper constructor.
     * @param PagevisitRepository $pagevisitRepository
     */
    public function __construct(PagevisitRepository $pagevisitRepository)
    {
        $this->pagevisitRepository = $pagevisitRepository;
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
    }

    /**
     * @return \DateTime|null
     * @throws Exception
     */
    public function render(): ?\DateTime
    {
        return $this->pagevisitRepository->findLatestDateByVisitor($this->arguments['visitor']);
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Lead;

use Doctrine\DBAL\Exception;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @param PagevisitRepository|null $pagevisitRepository
     */
    public function __construct(PagevisitRepository $pagevisitRepository = null)
    {
        $this->pagevisitRepository = $pagevisitRepository ?: GeneralUtility::makeInstance(PagevisitRepository::class);
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

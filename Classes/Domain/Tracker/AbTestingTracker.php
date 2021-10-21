<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\LogService;
use In2code\Luxenterprise\Domain\Model\Abpagevisit;
use In2code\Luxenterprise\Domain\Repository\AbpagevisitRepository;
use In2code\Luxenterprise\Domain\Repository\AbTestingPageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * AbTestingTracker
 */
class AbTestingTracker
{
    /**
     * @var Visitor|null
     */
    protected $visitor = null;

    /**
     * @var LogService|null
     */
    protected $logService = null;

    /**
     * @var AbTestingPageRepository|null
     */
    protected $abTestingPageRepository = null;

    /**
     * @var AbpagevisitRepository|null
     */
    protected $abpagevisitRepository = null;

    /**
     * Constructor
     *
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->logService = GeneralUtility::makeInstance(LogService::class);
        $this->abTestingPageRepository = GeneralUtility::makeInstance(AbTestingPageRepository::class);
        $this->abpagevisitRepository = GeneralUtility::makeInstance(AbpagevisitRepository::class);
    }

    /**
     * @param int $abTestingPageIdentifier
     * @return Abpagevisit
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function track(int $abTestingPageIdentifier): Abpagevisit
    {
        $abTestingPage = $this->abTestingPageRepository->findByIdentifier($abTestingPageIdentifier);
        $appagevisit = GeneralUtility::makeInstance(Abpagevisit::class);
        $appagevisit->setAbpage($abTestingPage)->setVisitor($this->visitor);
        $this->abpagevisitRepository->add($appagevisit);
        $this->abpagevisitRepository->persistAll();
        $this->logService->logAbTestingPage($this->visitor, $abTestingPage);
        return $appagevisit;
    }
}

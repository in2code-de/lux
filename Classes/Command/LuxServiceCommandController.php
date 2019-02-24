<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\ScoringService;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class LuxServiceCommandController
 */
class LuxServiceCommandController extends CommandController
{

    /**
     * Recalculate scoring of all visitors
     *
     *      Recalculate scoring of all visitors. Scoring calculation will be used from extension settings.
     *      You should run this task frequently (1 time a day) if you are using the variable {lastVisitDaysAgo}
     *      in your calculation
     *
     * @return void
     */
    public function reCalculateScoringCommand()
    {
        $scoringService = $this->objectManager->get(ScoringService::class);
        $visitorRepository = $this->objectManager->get(VisitorRepository::class);
        $visitors = $visitorRepository->findAll();
        foreach ($visitors as $visitor) {
            $scoringService->calculateAndSetScoring($visitor);
        }
    }
}

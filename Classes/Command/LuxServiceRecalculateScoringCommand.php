<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\ScoringService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class LuxServiceRecalculateScoringCommand extends Command
{
    use ExtbaseCommandTrait;

    public function configure()
    {
        $this->setDescription(
            'Recalculate scoring of all visitors. Scoring calculation will be used from extension settings.'
        );
    }

    /**
     * Recalculate scoring of all visitors
     *
     *      Recalculate scoring of all visitors. Scoring calculation will be used from extension settings.
     *      You should run this task frequently (1 time a day) if you are using the variable {lastVisitDaysAgo}
     *      in your calculation
     *
     *      Example command: ./vendor/bin/typo3 lux:serviceRecalculateScoring
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws IllegalObjectTypeException
     * @throws InvalidQueryException
     * @throws UnknownObjectException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeExtbase();
        $scoringService = GeneralUtility::makeInstance(ScoringService::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitors = $visitorRepository->findAll();
        foreach ($visitors as $visitor) {
            $scoringService->calculateAndSetScoring($visitor);
        }
        $output->writeln('Scoring recalculated of all visitors');
        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Repository\VisitorRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuxCleanupAllVisitorsCommand extends Command
{
    public function configure()
    {
        $this->setDescription('Remove all visitors and all collected data!');
    }

    /**
     * Remove all visitors!
     *
     *      Remove all visitors
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     *      Example command: ./vendor/bin/typo3 lux:cleanupAllVisitors
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitorRepository->truncateAll();
        $output->writeln('Every lux table successfully truncated');
        return self::SUCCESS;
    }
}

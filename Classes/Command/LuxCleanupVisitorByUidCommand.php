<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuxCleanupVisitorByUidCommand extends Command
{
    use ExtbaseCommandTrait;

    public function configure()
    {
        $this->setDescription('Remove visitor and all related data from the database');
        $this->addArgument('visitorUid', InputArgument::REQUIRED, 'visitor uid');
    }

    /**
     * Remove a visitor by a given UID
     *
     *      Remove a single visitor by a given UID
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     *      Example command: /vendor/bin/typo3 lux:cleanupVisitorByUid 123
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionDbal
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeExtbase();
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        /** @var Visitor $visitor */
        $visitor = $visitorRepository->findByUid((int)$input->getArgument('visitorUid'));
        $visitorRepository->removeVisitor($visitor);
        $output->writeln('Visitor successfully removed');
        return self::SUCCESS;
    }
}

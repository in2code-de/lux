<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\DateTimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class LuxCleanupVisitorsByAgeCommand extends Command
{
    use ExtbaseCommandTrait;

    public function configure()
    {
        $description = 'Remove all visitors where the last update is older than a given timestamp';
        $this->setDescription($description);
        $this->addArgument('timestamp', InputArgument::REQUIRED, 'timestamp to delete records that are older then');
    }

    /**
     * Remove all visitors where the last update is older than a given timestamp
     *
     *      Remove all visitors where the last update (tstamp) is older than a given timestamp
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     *       - absolute date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge 1583410470
     *       - absolute date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge '2010-01-01'
     *       - relative date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge 'First day of this month'
     *       - relative date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge 'First day of last month'
     *       - relative date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge 'First day of this year'
     *       - relative date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge -- '-30 days'
     *       - relative date: ./vendor/bin/typo3 lux:cleanupVisitorsByAge -- '-2678400 seconds'
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidQueryException
     * @throws ExceptionDbal
     * @throws DateTimeException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeExtbase();
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitors = $visitorRepository->findByLastChange($this->parseTime($input->getArgument('timestamp')));
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $visitorRepository->removeVisitor($visitor);
        }
        $output->writeln(count($visitors) . ' successfully removed');
        return self::SUCCESS;
    }
}

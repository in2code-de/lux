<?php
declare(strict_types = 1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxCleanupAllVisitorsCommand
 */
class LuxCleanupAllVisitorsCommand extends Command
{
    /**
     * @return void
     */
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
     * @throws Exception
     * @throws DBALException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $visitorRepository->truncateAll();
        $output->writeln('Every lux table successfully truncated');
        return 0;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxCleanupUnknownVisitorsByAgeCommand
 */
class LuxCleanupUnknownVisitorsByAgeCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $description = 'Remove all unknown visitors where the last update (tstamp) is older than a given timestamp';
        $this->setDescription($description);
        $this->addArgument('timestamp', InputArgument::REQUIRED, 'timestamp to delete records that are older then');
    }

    /**
     * Remove all unknown visitors where the last update is older than a given timestamp
     *
     *      Remove all unknown visitors where the last update (tstamp) is older than a given timestamp
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     *      Example command: /vendor/bin/typo3 lux:cleanupUnknownVisitorsByAge 1583410470
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $visitors = $visitorRepository->findByLastChangeUnknown((int)$input->getArgument('timestamp'));
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $visitorRepository->removeRelatedTableRowsByVisitor($visitor);
            $visitorRepository->removeVisitor($visitor);
        }
        $output->writeln(count($visitors) . ' successfully removed');
        return 0;
    }
}

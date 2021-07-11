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
 * Class LuxCleanupVisitorsByPropertyCommand
 */
class LuxCleanupVisitorsByPropertyCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $description = 'Remove a visitor by a given property. E.g. removing all google bots with';
        $this->setDescription($description);
        $this->addArgument('propertyName', InputArgument::REQUIRED, 'any property name');
        $this->addArgument('propertyValue', InputArgument::REQUIRED, 'any property value');
        $this->addArgument('exactMatch', InputArgument::OPTIONAL, 'direct match');
    }

    /**
     * Remove a visitor by a given property. E.g. removing all google bots with
     * "./vendor/bin/typo3 lux:cleanupVisitorsByProperty userAgent Googlebot 0" or
     * "./vendor/bin/typo3 lux:cleanupVisitorsByProperty fingerprints.userAgent "Ubuntu; Linux" 0"
     *
     *      Remove a visitor by a given property. E.g. removing all
     *      !!! Really removes visitors and all rows from related tables from the database
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
        $visitors = $visitorRepository->findAllByProperty(
            (string)$input->getArgument('propertyName'),
            (string)$input->getArgument('propertyValue'),
            (bool)$input->getArgument('exactMatch')
        );
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $visitorRepository->removeRelatedTableRowsByVisitor($visitor);
            $visitorRepository->removeVisitor($visitor);
        }
        $output->writeln(count($visitors) . ' successfully removed');
        return 0;
    }
}

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
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class LuxCleanupVisitorsByPropertyCommand extends Command
{
    use ExtbaseCommandTrait;

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
     * @throws InvalidQueryException
     * @throws ExceptionDbal
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeExtbase();
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitors = $visitorRepository->findAllByProperty(
            (string)$input->getArgument('propertyName'),
            (string)$input->getArgument('propertyValue'),
            (bool)$input->getArgument('exactMatch')
        );
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $visitorRepository->removeVisitor($visitor);
        }
        $output->writeln(count($visitors) . ' successfully removed');
        return self::SUCCESS;
    }
}

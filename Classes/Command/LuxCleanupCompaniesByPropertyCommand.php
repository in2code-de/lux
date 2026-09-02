<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Repository\CompanyRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class LuxCleanupCompaniesByPropertyCommand extends Command
{
    use ExtbaseCommandTrait;

    public function configure()
    {
        $description = 'Remove companies by a given property. E.g. removing all companies of a country';
        $this->setDescription($description);
        $this->addArgument('propertyName', InputArgument::REQUIRED, 'any property name');
        $this->addArgument('propertyValue', InputArgument::REQUIRED, 'any property value');
        $this->addArgument('exactMatch', InputArgument::OPTIONAL, 'direct match');
        $this->addOption('remove-visitors', null, InputOption::VALUE_NONE, 'also remove all related visitors');
    }

    /**
     * Remove companies by a given property. E.g. removing all chinese companies with
     * "./vendor/bin/typo3 lux:cleanupCompaniesByProperty countryCode cn 1" or
     * "./vendor/bin/typo3 lux:cleanupCompaniesByProperty title "Testfirma" 0 --remove-visitors"
     *
     *      !!! Really removes companies from the database
     *      Relations to a removed company are always resolved: with --remove-visitors all related visitors and
     *      all rows from their related tables are removed, without it the visitors are kept and only lose their
     *      relation to the company
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
        $companyRepository = GeneralUtility::makeInstance(CompanyRepository::class);
        $companies = $companyRepository->findAllByProperty(
            (string)$input->getArgument('propertyName'),
            (string)$input->getArgument('propertyValue'),
            (bool)$input->getArgument('exactMatch')
        );
        $removeVisitors = (bool)$input->getOption('remove-visitors');
        /** @var Company $company */
        foreach ($companies as $company) {
            $companyRepository->removeCompany($company, $removeVisitors);
        }
        $output->writeln(count($companies) . ' successfully removed');
        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use DateTime;
use In2code\Lux\Domain\Service\CompanyInformationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuxSetWiredmindCompanyInformationCommand extends Command
{
    public function configure()
    {
        $this->setDescription('Add company records from Wiredminds from existing IP addresses in visitor records');
        $descriptionLimit = 'Search for the latest X leads and try to extend them with company information. ' .
            'But: Not every of this IP addresses leads to a company record. ' .
            'So 100 means 100 requests to Wiredminds and maybe 10 new company records.';
        $this->addOption('limit', 'l', InputArgument::OPTIONAL, $descriptionLimit, '0');
        $this->addOption(
            'overwriteexisting',
            'o',
            InputArgument::OPTIONAL,
            'Overwrite existing company records with newer information',
            '0'
        );
        $this->addOption('time', 't', InputArgument::OPTIONAL, 'Try to convert only leads in this timeframe', '- 5 years');
    }

    /**
     * Add company records from Wiredminds from existing IP addresses in visitor records
     *
     *      Example command: ./vendor/bin/typo3 lux:setWiredmindCompanyInformation
     *      Example command: ./vendor/bin/typo3 lux:setWiredmindCompanyInformation -l 50 -o 1 --time="-300000 seconds"
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $companyInformationService = GeneralUtility::makeInstance(CompanyInformationService::class);
            $count = $companyInformationService->setCompaniesToExistingVisitors(
                (int)$input->getOption('limit') ?: 10000,
                (bool)$input->getOption('overwriteexisting'),
                (new DateTime($input->getOption('time'))),
                $output
            );
            $output->writeln(PHP_EOL . $count . ' hits! Leads extended with company records');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $output->writeln($exception->getMessage());
        }
        return self::FAILURE;
    }
}

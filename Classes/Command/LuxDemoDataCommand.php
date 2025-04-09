<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Service\DemoDataService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuxDemoDataCommand extends Command
{
    public function configure()
    {
        $description = 'Delete all existing LUX data and put in some demo data. For demonstration cases only.';
        $this->setDescription($description);
    }

    /**
     * Example command: ./vendor/bin/typo3 lux:demodata
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var DemoDataService $demoDataService */
        $demoDataService = GeneralUtility::makeInstance(DemoDataService::class);
        $demoDataService->write();

        $output->writeln('All data deleted. After that, demonstration data added to all LUX tables!');
        return parent::SUCCESS;
    }
}

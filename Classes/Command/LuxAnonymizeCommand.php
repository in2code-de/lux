<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Service\AnonymizeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LuxCleanupCommandController
 */
class LuxAnonymizeCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $description = 'This command will really (!!!) overwrite all your records with dummy values.' . PHP_EOL;
        $description .= 'This command is for local development or for a presentation only!';
        $this->setDescription($description);
    }

    /**
     * This command will really (!!!) overwrite all your identified-visitor-records with dummy values.
     * This command is e.g. for local development or for a presentation!
     *
     *      Example command: ./vendor/bin/typo3 lux:anonymize
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $anonymizeService = GeneralUtility::makeInstance(AnonymizeService::class);
        $anonymizeService->anonymizeAll();
        $output->writeln('Everything was anonymized!');
        return 0;
    }
}

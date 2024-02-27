<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Service\AnonymizeService;
use In2code\Lux\Exception\ConfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuxAnonymizeCommand extends Command
{
    public function configure()
    {
        $description = 'This command will really (!!!) overwrite all your records with dummy values.' . PHP_EOL;
        $description .= 'This command is for local development or for a presentation only!';
        $this->setDescription($description);
    }

    /**
     * This command will really (!!!) overwrite all your identified-visitor-records and companies with dummy values.
     * This command is e.g. for a gdpr respecting environment, local development or for a presentation!
     *
     *      Example command: ./vendor/bin/typo3 lux:anonymize
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     * @throws ConfigurationException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $anonymizeService = GeneralUtility::makeInstance(AnonymizeService::class);
        $anonymizeService->anonymizeAll();
        $output->writeln('Everything was anonymized!');
        return self::SUCCESS;
    }
}

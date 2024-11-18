<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Exception;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\Email\SendSummaryService;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class LuxLeadSendSummaryCommand extends Command
{
    use ExtbaseCommandTrait;

    public function configure()
    {
        $description = 'Send a summary of leads to one or more email addresses as a table.' .
            ' Define if leads should be identified or not and if they should have a minimum scoring to be sent.';
        $this->setDescription($description);
        $this->addArgument('emails', InputArgument::REQUIRED, 'Commaseparated value of email addresses as receivers');
        $this->addArgument(
            'timeframe',
            InputArgument::OPTIONAL,
            '86400 means all leads from the last 24h',
            DateUtility::SECONDS_DAY
        );
        $this->addArgument('identified', InputArgument::OPTIONAL, 'Identified leads only?', FilterDto::IDENTIFIED_ALL);
        $this->addArgument('minimumScoring', InputArgument::OPTIONAL, 'Leads with a minimum scoring?', 0);
    }

    /**
     * Send a summary of leads
     *
     *      Send a summary of leads to one or more email addresses as a table. Define if leads should be identified or
     *      not and if they should have a minimum scoring to be sent.
     *
     *      Example command: ./vendor/bin/typo3 lux:leadSendSummary alex@mail.org 86400 1
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidQueryException
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeExtbase();
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto();
        $filter
            ->setTimeFrame((int)$input->getArgument('timeframe'))
            ->setScoring((int)$input->getArgument('minimumScoring'))
            ->setIdentified((int)$input->getArgument('identified'))
            ->setLimit(750);
        $visitors = $visitorRepository->findAllWithIdentifiedFirst($filter);

        if (count($visitors) > 0) {
            $sendSummaryService = GeneralUtility::makeInstance(SendSummaryService::class, $visitors);
            $result = $sendSummaryService->send(GeneralUtility::trimExplode(',', $input->getArgument('emails'), true));
            if ($result === true) {
                $output->writeln('Mail with ' . count($visitors) . ' leads successfully sent');
            } else {
                $output->writeln('Mail could not be sent. Please check your configuration');
            }
        } else {
            $output->writeln('No active leads found in given timeframe');
        }
        return self::SUCCESS;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\Email\SendSummaryService;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxLeadSendSummaryOfLuxCategoryCommand
 */
class LuxLeadSendSummaryOfLuxCategoryCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $description = 'Send a summary of leads to one or more email addresses as a table.' .
            ' Define if leads should be identified or not and if you want only leads from a given category.';
        $this->setDescription($description);
        $this->addArgument('emails', InputArgument::REQUIRED, 'Commaseparated value of email addresses as receivers');
        $this->addArgument('timeframe', InputArgument::OPTIONAL, '86400 means all leads from the last 24h', 86400);
        $this->addArgument('identified', InputArgument::OPTIONAL, 'Identified leads only?', FilterDto::IDENTIFIED_ALL);
        $this->addArgument('luxCategory', InputArgument::OPTIONAL, 'Send only leads with a scoring in category', 0);
    }

    /**
     * Send a summary of leads of a lux category
     *
     *      Send a summary of leads to one or more email addresses as a table. Define if leads should be identified or
     *      not and if you want only leads from a given category. Also a minimum scoring is possible.
     *
     *      Example command: ./vendor/bin/typo3 lux:leadSendSummaryOfLuxCategory alex@mail.org 86400 1 123
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     * @throws InvalidQueryException
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto();
        $filter
            ->setTimeFrame((int)$input->getArgument('timeframe'))
            ->setIdentified((int)$input->getArgument('identified'))
            ->setCategoryScoring((int)$input->getArgument('luxCategory'));
        $visitors = $visitorRepository->findAllWithIdentifiedFirst($filter);

        if ($visitors->count() > 0) {
            /** @var SendSummaryService $sendSummaryService */
            $sendSummaryService = ObjectUtility::getObjectManager()->get(SendSummaryService::class, $visitors);
            $result = $sendSummaryService->send(GeneralUtility::trimExplode(',', $input->getArgument('emails'), true));
            if ($result === true) {
                $output->writeln('Mail with ' . $visitors->count() . ' leads successfully sent');
            } else {
                $output->writeln('Mail could not be sent. Please check your configuration');
            }
        } else {
            $output->writeln('No active leads found in given timeframe');
        }
        return 0;
    }
}

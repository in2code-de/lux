<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\SendSummaryService;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxLeadCommandController
 */
class LuxLeadCommandController extends CommandController
{

    /**
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * Send a summary of leads
     *
     *      Send a summary of leads to one or more email addresses as a table. Define if leads should be identified or
     *      not and if they should have a minimum scoring to be sent.
     *
     * @param string $emails Commaseparated value of email addresses for receiving mails
     * @param int $timeframe Timeframe for changed properties in leads. 86400 means all leads from the last 24h.
     * @param int $identified Send only identified leads, see FilterDto::IDENTIFIED_ status
     * @param int $minimumScoring Send only leads with a minimum scoring of this value.
     * @return void
     * @throws InvalidQueryException
     */
    public function sendSummaryCommand(
        string $emails,
        int $timeframe = 86400,
        int $identified = FilterDto::IDENTIFIED_ALL,
        int $minimumScoring = 0
    ) {
        /** @var FilterDto $filter */
        $filter = ObjectUtility::getFilterDto();
        $filter
            ->setTimeFrame($timeframe)
            ->setScoring($minimumScoring)
            ->setIdentified($identified);
        $visitors = $this->visitorRepository->findAllWithIdentifiedFirst($filter);

        if ($visitors->count() > 0) {
            /** @var SendSummaryService $sendSummaryService */
            $sendSummaryService = $this->objectManager->get(SendSummaryService::class, $visitors);
            $result = $sendSummaryService->send(GeneralUtility::trimExplode(',', $emails, true));
            if ($result === true) {
                $this->outputLine('Mail with ' . $visitors->count() . ' leads successfully sent');
            } else {
                $this->outputLine('Mail could not be sent. Please check your configuration');
            }
        } else {
            $this->outputLine('No active leads found in given timeframe');
        }
    }

    /**
     * Send a summary of leads of a lux category
     *
     *      Send a summary of leads to one or more email addresses as a table. Define if leads should be identified or
     *      not and if you want only leads from a given category. Also a minimum scoring is possible.
     *
     * @param string $emails Commaseparated value of email addresses for receiving mails.
     * @param int $timeframe Timeframe for changed properties in leads. 86400 means all leads from the last 24h.
     * @param int $identified Send only identified leads, see FilterDto::IDENTIFIED_ status
     * @param int $luxCategory Send only leads that have a scoring in this category.
     * @return void
     * @throws InvalidQueryException
     */
    public function sendSummaryOfLuxCategoryCommand(
        string $emails,
        int $timeframe = 86400,
        int $identified = FilterDto::IDENTIFIED_ALL,
        int $luxCategory = 0
    ) {
        /** @var FilterDto $filter */
        $filter = ObjectUtility::getFilterDto();
        $filter
            ->setTimeFrame($timeframe)
            ->setIdentified($identified)
            ->setCategoryScoring($luxCategory);
        $visitors = $this->visitorRepository->findAllWithIdentifiedFirst($filter);

        if ($visitors->count() > 0) {
            /** @var SendSummaryService $sendSummaryService */
            $sendSummaryService = $this->objectManager->get(SendSummaryService::class, $visitors);
            $result = $sendSummaryService->send(GeneralUtility::trimExplode(',', $emails, true));
            if ($result === true) {
                $this->outputLine('Mail with ' . $visitors->count() . ' leads successfully sent');
            } else {
                $this->outputLine('Mail could not be sent. Please check your configuration');
            }
        } else {
            $this->outputLine('No active leads found in given timeframe');
        }
    }

    /**
     * Send a summary of leads with known companies
     *
     *      Send a summary of leads with known companies to one or more email addresses as a table. Define if leads
     *      should have a minimum scoring (0 disables this function). Also define if only leads should be send with
     *      a scoring in a category.
     *
     * @param string $emails Commaseparated value of email addresses for receiving mails.
     * @param int $timeframe Timeframe for changed properties in leads. 86400 means all leads from the last 24h.
     * @param int $minimumScoring Send only leads with a minimum scoring of this value.
     * @param int $luxCategory Send only leads that have a scoring in this category (0 disables this feature).
     * @return void
     * @throws InvalidQueryException
     */
    public function sendSummaryOfKnownCompaniesCommand(
        string $emails,
        int $timeframe = 86400,
        int $minimumScoring = 0,
        int $luxCategory = 0
    ) {
        /** @var FilterDto $filter */
        $filter = ObjectUtility::getFilterDto();
        $filter
            ->setTimeFrame($timeframe)
            ->setScoring($minimumScoring)
            ->setCategoryScoring($luxCategory);
        $visitors = $this->visitorRepository->findAllWithKnownCompanies($filter);

        if (count($visitors) > 0) {
            /** @var SendSummaryService $sendSummaryService */
            $sendSummaryService = $this->objectManager->get(SendSummaryService::class, $visitors);
            $result = $sendSummaryService->send(GeneralUtility::trimExplode(',', $emails, true));
            if ($result === true) {
                $this->outputLine('Mail with ' . count($visitors) . ' leads successfully sent');
            } else {
                $this->outputLine('Mail could not be sent. Please check your configuration');
            }
        } else {
            $this->outputLine('No active leads found in given timeframe');
        }
    }

    /**
     * @param VisitorRepository $visitorRepository
     * @return void
     */
    public function injectVisitorRepository(VisitorRepository $visitorRepository)
    {
        $this->visitorRepository = $visitorRepository;
    }
}

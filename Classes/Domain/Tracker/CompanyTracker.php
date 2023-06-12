<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use DateTime;
use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\Remote\WiredmindsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * Class CompanyTracker to enrich visitor with company information from wiredminds.com via IP address
 *
 * but only if:
 * - visitor is not blacklisted
 * - this tracking is activated via TS
 * - there is no related companyrecord yet
 * - and the latest pagevisit must be empty (for new visitors) or some time ago
 */
class CompanyTracker
{
    protected VisitorRepository $visitorRepository;
    protected WiredmindsRepository $wiredmindsRepository;
    protected RequestFactory $requestFactory;
    protected CompanyFactory $companyFactory;

    protected array $settings = [];

    /**
     * To prevent that a visitor with anonymous IP leads to a lot of interfarce requests, we define a waiting period
     * that has to be passed, before we connect the interface again
     *
     * @var string
     */
    protected string $interfaceWaitPeriod = '-30 days';

    public function __construct(
        VisitorRepository $visitorRepository,
        WiredmindsRepository $wiredmindsRepository,
        RequestFactory $requestFactory,
        CompanyFactory $companyFactory
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->wiredmindsRepository = $wiredmindsRepository;
        $this->requestFactory = $requestFactory;
        $this->companyFactory = $companyFactory;
        $configurationService = ObjectUtility::getConfigurationService();
        $this->settings = $configurationService->getTypoScriptSettings();
    }

    public function track(Visitor $visitor): void
    {
        if ($this->isTrackingActivated($visitor)) {
            $properties = $this->wiredmindsRepository->getPropertiesForIpAddress($visitor);
            if ($properties !== []) {
                $this->persistCompany($visitor, $properties);
            }
        }
    }

    protected function persistCompany(Visitor $visitor, array $properties): void
    {
        $company = $this->companyFactory->getExistingOrNewPersistedCompany($properties);
        $visitor->setCompanyrecord($company);
        $this->visitorRepository->update($visitor);
        $this->visitorRepository->persistAll();
    }

    protected function isTrackingActivated(Visitor $visitor): bool
    {
        return $visitor->isNotBlacklisted()
            && $this->isTrackingActivatedInSettings()
            && $visitor->getCompanyrecord() === null
            && $this->isWaitPeriodRespected($visitor);
    }

    protected function isTrackingActivatedInSettings(): bool
    {
        return isset($this->settings['tracking']['company']['_enable'])
            && $this->settings['tracking']['company']['_enable'] === '1';
    }

    protected function isWaitPeriodRespected(Visitor $visitor): bool
    {
        return $visitor->getLastPagevisit() === null ||
            $visitor->getLastPagevisit()->getCrdate() < (new DateTime())->modify($this->interfaceWaitPeriod);
    }
}

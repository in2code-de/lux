<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use DateTime;
use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use Throwable;
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
    private const INTERFACE_URL = 'https://ip2c.wiredminds.com/';

    protected VisitorRepository $visitorRepository;
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
        RequestFactory $requestFactory,
        CompanyFactory $companyFactory
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->requestFactory = $requestFactory;
        $this->companyFactory = $companyFactory;
        $configurationService = ObjectUtility::getConfigurationService();
        $this->settings = $configurationService->getTypoScriptSettings();
    }

    public function track(Visitor $visitor): void
    {
        if ($this->isTrackingActivated($visitor)) {
            try {
                $result = $this->requestFactory->request($this->getUri());
                if ($result->getStatusCode() === 200) {
                    $properties = json_decode($result->getBody()->getContents(), true);
                    $this->persistCompany($visitor, $properties);
                }
            } catch (Throwable $exception) {
                // Don't persist company on (e.g.) 404 if IP could not be dissolved
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

    /**
     * @return string
     * @throws ConfigurationException
     */
    protected function getUri(): string
    {
        $token = trim($this->settings['tracking']['company']['token'] ?? '');
        if ($token === '') {
            throw new ConfigurationException('No wiredminds token defined in TypoScript', 1684433462);
        }
        return self::INTERFACE_URL . $token . '/' . IpUtility::getIpAddress();
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

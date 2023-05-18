<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * Class CompanyTracker
 * to enrich visitor with company information from wiredminds.com via IP address
 */
class CompanyTracker
{
    protected VisitorRepository $visitorRepository;
    protected RequestFactory $requestFactory;
    protected CompanyFactory $companyFactory;

    protected array $settings = [];

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
            if ($visitor->getCompanyrecord() === null) {
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
        return 'https://ip2c.wiredminds.com/' . $token . '/' . IpUtility::getIpAddress();
    }

    protected function isTrackingActivated(Visitor $visitor): bool
    {
        return $visitor->isNotBlacklisted() && $this->isTrackingActivatedInSettings();
    }

    protected function isTrackingActivatedInSettings(): bool
    {
        return isset($this->settings['tracking']['company']['_enable'])
            && $this->settings['tracking']['company']['_enable'] === '1';
    }
}

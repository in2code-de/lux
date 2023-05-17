<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\CompanyRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Class CompanyTracker
 * to enrich visitor with company information from wiredminds.com via IP address
 */
class CompanyTracker
{
    protected VisitorRepository $visitorRepository;
    protected CompanyRepository $companyRepository;
    protected RequestFactory $requestFactory;
    protected DataMapper $dataMapper;

    protected array $settings = [];

    public function __construct(
        VisitorRepository $visitorRepository,
        CompanyRepository $companyRepository,
        RequestFactory $requestFactory,
        DataMapper $dataMapper
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->companyRepository = $companyRepository;
        $this->requestFactory = $requestFactory;
        $this->dataMapper = $dataMapper;
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
        $properties['title'] = $properties['name']; // map title
        $company = $this->companyRepository->findByTitleAndDomain($properties['title'], $properties['domain']);
        if ($company === null) {
            $company = $this->getNewCompany($properties);
        }
        $visitor->setCompanyrecord($company);
        $this->visitorRepository->update($visitor);
        $this->visitorRepository->persistAll();
    }

    protected function getNewCompany(array $properties): Company
    {
        $properties['uid'] = 0; // avoid missing array key error in extbase
        $properties['contacts'] = json_encode($properties['contacts']);

        /** @var Company $company */
        $company = $this->dataMapper->map(Company::class, [$properties])[0];
        $company->_setProperty('uid', null); // reset uid from 0 to null
        $company->_memorizePropertyCleanState('uid'); // tell datamapper that this is a new record

        $this->companyRepository->add($company);
        $this->companyRepository->persistAll();

        return $company;
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

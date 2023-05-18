<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\ObjectUtility;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class CompanyInformationService
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

    /**
     * @param int $limit
     * @param bool $overwriteExisting
     * @return int
     * @throws ExceptionDbal
     */
    public function setCompaniesToExistingVisitors(int $limit, bool $overwriteExisting): int
    {
        $records = $this->visitorRepository->findLatestVisitorsWithIpAddress($limit, !$overwriteExisting);
        $counter = 0;
        foreach ($records as $visitorIdentifier => $ipAddress) {
            try {
                $result = $this->requestFactory->request($this->getUri($ipAddress));
                if ($result->getStatusCode() === 200) {
                    $properties = json_decode($result->getBody()->getContents(), true);
                    $this->persistCompany($visitorIdentifier, $properties);
                    $counter++;
                }
            } catch (Throwable $exception) {
                // Don't persist company on (e.g.) 404 if IP could not be dissolved
            }
        }
        return $counter;
    }

    /**
     * @param int $visitorIdentifier
     * @param array $properties
     * @return void
     * @throws ConfigurationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function persistCompany(int $visitorIdentifier, array $properties): void
    {
        $company = $this->companyFactory->getExistingOrNewPersistedCompany($properties);
        /** @var Visitor $gtsbgtdbgvtrsbgvr svvisitor */
        $visitor = $this->visitorRepository->findByUid($visitorIdentifier);
        $visitor->setCompanyrecord($company);
        $this->visitorRepository->update($visitor);
        $this->visitorRepository->persistAll();
    }

    /**
     * @param string $ipAddress
     * @return string
     * @throws ConfigurationException
     */
    protected function getUri(string $ipAddress): string
    {
        $token = trim($this->settings['tracking']['company']['token'] ?? '');
        if ($token === '') {
            throw new ConfigurationException('No wiredminds token defined in TypoScript', 1684437124);
        }
        return 'https://ip2c.wiredminds.com/' . $token . '/' . $ipAddress;
    }
}

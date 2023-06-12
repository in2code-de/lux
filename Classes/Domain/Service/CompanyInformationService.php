<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\Remote\WiredmindsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class CompanyInformationService
{
    protected VisitorRepository $visitorRepository;
    protected WiredmindsRepository $wiredmindsRepository;
    protected RequestFactory $requestFactory;
    protected CompanyFactory $companyFactory;

    protected array $settings = [];

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

    /**
     * @param int $limit
     * @param bool $overwriteExisting
     * @return int
     * @throws ConfigurationException
     * @throws ExceptionDbal
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function setCompaniesToExistingVisitors(int $limit, bool $overwriteExisting): int
    {
        $records = $this->visitorRepository->findLatestVisitorsWithIpAddress($limit, !$overwriteExisting);
        $counter = 0;
        foreach ($records as $visitorIdentifier => $ipAddress) {
            $visitor = $this->visitorRepository->findByUid($visitorIdentifier);
            if ($visitor !== null) {
                $properties = $this->wiredmindsRepository->getPropertiesForIpAddress($visitor, $ipAddress);
                if ($properties !== []) {
                    $this->persistCompany($visitorIdentifier, $properties);
                    $counter++;
                }
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
        /** @var Visitor $visitor */
        $visitor = $this->visitorRepository->findByUid($visitorIdentifier);
        $visitor->setCompanyrecord($company);
        $this->visitorRepository->update($visitor);
        $this->visitorRepository->persistAll();
    }
}

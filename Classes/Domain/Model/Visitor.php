<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use Exception;
use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Repository\CategoryscoringRepository;
use In2code\Lux\Domain\Repository\FrontendUserRepository;
use In2code\Lux\Domain\Repository\Remote\WiredmindsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\GetCompanyFromIpService;
use In2code\Lux\Domain\Service\Image\VisitorImageService;
use In2code\Lux\Domain\Service\Provider\Telecommunication;
use In2code\Lux\Domain\Service\ScoringService;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\EnvironmentUtility;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use In2code\Luxenterprise\Domain\Model\Abpagevisit;
use In2code\Luxenterprise\Domain\Repository\AbpagevisitRepository;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Visitor extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_visitor';
    public const IMPORTANT_ATTRIBUTES = [
        'email',
        'firstname',
        'lastname',
        'company',
        'username',
    ];

    protected int $scoring = 0;

    /**
     * @var ?ObjectStorage<Categoryscoring>
     * @Lazy
     */
    protected ?ObjectStorage $categoryscorings = null;

    /**
     * @Lazy
     * @var ?Fingerprint|LazyLoadingProxy
     * @phpstan-var ObjectStorage|LazyLoadingProxy|null
     */
    protected Fingerprint|LazyLoadingProxy|null $fingerprints = null;

    protected string $email = '';
    protected string $company = '';

    /**
     * @Lazy
     * @var Company|LazyLoadingProxy|null
     * @phpstan-var Company|LazyLoadingProxy|null
     */
    protected Company|LazyLoadingProxy|null $companyrecord = null;

    protected bool $identified = false;

    protected int $visits = 0;

    /**
     * @var ?ObjectStorage<Pagevisit>
     * @Lazy
     */
    protected ?ObjectStorage $pagevisits = null;

    /**
     * @var ?ObjectStorage<Newsvisit>
     * @Lazy
     */
    protected ?ObjectStorage $newsvisits = null;

    /**
     * @var ?ObjectStorage<Linkclick>
     * @Lazy
     */
    protected ?ObjectStorage $linkclicks = null;

    /**
     * @var ?ObjectStorage<Attribute>
     */
    protected ?ObjectStorage $attributes = null;

    protected string $ipAddress = '';

    /**
     * @var ?ObjectStorage<Ipinformation>
     * @Lazy
     */
    protected ?ObjectStorage $ipinformations = null;

    /**
     * @var ?ObjectStorage<Download>
     * @Lazy
     */
    protected ?ObjectStorage $downloads = null;

    /**
     * @var ?ObjectStorage<Log>
     * @Lazy
     */
    protected ?ObjectStorage $logs = null;

    protected ?DateTime $crdate = null;
    protected ?DateTime $tstamp = null;

    protected string $description = '';

    protected bool $blacklisted = false;

    protected ?FrontendUser $frontenduser = null;

    public function __construct()
    {
        parent::__construct();
        $this->categoryscorings = new ObjectStorage();
        $this->fingerprints = new ObjectStorage();
        $this->pagevisits = new ObjectStorage();
        $this->newsvisits = new ObjectStorage();
        $this->linkclicks = new ObjectStorage();
        $this->attributes = new ObjectStorage();
        $this->ipinformations = new ObjectStorage();
        $this->downloads = new ObjectStorage();
        $this->logs = new ObjectStorage();
    }

    public function getScoring(): int
    {
        return $this->scoring;
    }

    public function setScoring(int $scoring): self
    {
        $this->scoring = $scoring;
        return $this;
    }

    /**
     * Get the scoring to any given time in the past
     *
     * @param DateTime $time
     * @return int
     * @throws InvalidQueryException
     */
    public function getScoringByDate(DateTime $time): int
    {
        $scoringService = GeneralUtility::makeInstance(ScoringService::class, $time);
        return $scoringService->calculateScoring($this);
    }

    public function getCategoryscorings(): ObjectStorage
    {
        return $this->categoryscorings;
    }

    public function setCategoryscorings(ObjectStorage $categoryscorings): self
    {
        $this->categoryscorings = $categoryscorings;
        return $this;
    }

    public function addCategoryscoring(Categoryscoring $categoryscoring): self
    {
        $this->categoryscorings->attach($categoryscoring);
        return $this;
    }

    public function removeCategoryscoring(Categoryscoring $categoryscoring): self
    {
        $this->categoryscorings->detach($categoryscoring);
        return $this;
    }

    public function getCategoryscoringsSortedByScoring(): array
    {
        $categoryscorings = $this->getCategoryscorings()->toArray();
        usort($categoryscorings, [$this, 'getCategoryscoringsSortedByScoringCallback']);
        return $categoryscorings;
    }

    public function getCategoryscoringByCategory(Category $category): ?Categoryscoring
    {
        $categoryscorings = $this->getCategoryscorings();
        /** @var Categoryscoring $categoryscoring */
        foreach ($categoryscorings as $categoryscoring) {
            if ($categoryscoring->getCategory() === $category) {
                return $categoryscoring;
            }
        }
        return null;
    }

    public function getHottestCategoryscoring(): ?Categoryscoring
    {
        $categoryscorings = $this->getCategoryscorings()->toArray();
        uasort($categoryscorings, [$this, 'sortForHottestCategoryscoring']);
        $firstCs = array_slice($categoryscorings, 0, 1);
        if (!empty($firstCs[0])) {
            return $firstCs[0];
        }
        return null;
    }

    public function setCategoryscoringByCategory(int $scoring, Category $category): void
    {
        $csRepository = GeneralUtility::makeInstance(CategoryscoringRepository::class);
        $categoryscoring = $this->getCategoryscoringByCategory($category);
        if ($categoryscoring !== null) {
            $categoryscoring->setScoring($scoring);
            $csRepository->update($categoryscoring);
        } else {
            $categoryscoring = GeneralUtility::makeInstance(Categoryscoring::class);
            $categoryscoring->setCategory($category);
            $categoryscoring->setScoring($scoring);
            $categoryscoring->setVisitor($this);
            $csRepository->add($categoryscoring);
            $this->addCategoryscoring($categoryscoring);
        }
        $csRepository->persistAll();
    }

    public function increaseCategoryscoringByCategory(int $value, Category $category): void
    {
        $scoring = 0;
        if ($this->getCategoryscoringByCategory($category) !== null) {
            $scoring = $this->getCategoryscoringByCategory($category)->getScoring();
        }
        $newScoring = $scoring + $value;
        $this->setCategoryscoringByCategory($newScoring, $category);
    }

    public function getFingerprints(): array
    {
        $fingerprints = $this->fingerprints instanceof LazyLoadingProxy
            ? $this->fingerprints->_loadRealInstance()
            : $this->fingerprints;
        $fpArray = $fingerprints->toArray();
        foreach ($fpArray as $key => $fingerprint) {
            if ($fingerprint->canBeRead() === false) {
                unset($fpArray[$key]);
            }
        }
        return $fpArray;
    }

    /**
     * Get related fingerprints sorted with the latest first
     *
     * @return array
     */
    public function getFingerprintsSorted(): array
    {
        $fingerprints = $this->getFingerprints();
        return array_reverse($fingerprints);
    }

    public function getLatestFingerprint(): ?Fingerprint
    {
        $fingerprints = $this->getFingerprints();
        foreach ($fingerprints as $fingerprint) {
            return $fingerprint;
        }
        return null;
    }

    public function getFingerprintValues(): array
    {
        $values = [];
        foreach ($this->getFingerprints() as $fingerprint) {
            $values[] = $fingerprint->getValue();
        }
        return $values;
    }

    public function setFingerprints(ObjectStorage $fingerprints): self
    {
        $this->fingerprints = $fingerprints;
        return $this;
    }

    public function addFingerprint(Fingerprint $fingerprint): self
    {
        $this->fingerprints->attach($fingerprint);
        return $this;
    }

    public function addFingerprints(ObjectStorage $fingerprints): self
    {
        foreach ($fingerprints as $fingerprint) {
            /** @var Fingerprint $fingerprint */
            $this->addFingerprint($fingerprint);
        }
        return $this;
    }

    public function removeFingerprint(Fingerprint $fingerprint): self
    {
        $this->fingerprints->detach($fingerprint);
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCompany(): string
    {
        $company = $this->company;
        if (empty($company)) {
            $company = $this->getPropertyFromAttributes('company');
        }
        if (empty($company)) {
            $company = $this->getPropertyFromCompanyrecord('title');
        }
        if (empty($company)) {
            $companyFromIp = GeneralUtility::makeInstance(GetCompanyFromIpService::class);
            $company = $companyFromIp->get($this);
            if (empty($company)) {
                $company = $this->getPropertyFromIpinformations('org');
                $tcProvider = GeneralUtility::makeInstance(Telecommunication::class);
                if ($tcProvider->isTelecommunicationProvider($company)) {
                    $company = '';
                }
            }
        }
        return $company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Set visitor.company property from attribute, companyrecord or IP information if possible
     *
     * @return $this
     */
    public function resetCompanyAutomatic(): self
    {
        $this->company = '';
        $company = $this->getCompany();
        if ($company !== '') {
            $this->company = $company;
        }
        return $this;
    }

    public function getCompanyrecord(): ?Company
    {
        return $this->companyrecord instanceof LazyLoadingProxy
            ? $this->companyrecord->_loadRealInstance()
            : $this->companyrecord;
    }

    public function setCompanyrecord(?Company $companyrecord): self
    {
        $this->companyrecord = $companyrecord;

        // New calculation of visitor.company property if a companyrecord was set
        $this->resetCompanyAutomatic();

        return $this;
    }

    public function resetCompanyrecord(): self
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder
            ->update(self::TABLE_NAME)
            ->where('uid=' . $this->getUid())->set('companyrecord', 0)
            ->executeStatement();
        $this->companyrecord = null;
        return $this;
    }

    /**
     * @param string $ipAddress use current IP address when empty
     * @return bool return if there was a hit on wiredminds
     * @throws ConfigurationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function setCompanyrecordByIpAdressFromInterface(string $ipAddress = ''): bool
    {
        $wiredmindsRepository = GeneralUtility::makeInstance(WiredmindsRepository::class);
        $properties = $wiredmindsRepository->getPropertiesForIpAddress($this, $ipAddress);
        if ($properties !== []) {
            $companyFactory = GeneralUtility::makeInstance(CompanyFactory::class);
            $company = $companyFactory->getExistingOrNewPersistedCompany($properties);
            $this->setCompanyrecord($company);
            $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
            $visitorRepository->update($this);
            $visitorRepository->persistAll();
            return true;
        }
        return false;
    }

    public function getPropertyFromCompanyrecord(string $property): string
    {
        $companyrecord = $this->getCompanyrecord();
        if ($companyrecord !== null) {
            return (string)$companyrecord->_getProperty($property);
        }
        return '';
    }

    public function isIdentified(): bool
    {
        return $this->identified;
    }

    public function setIdentified(bool $identified): self
    {
        $this->identified = $identified;
        return $this;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function setVisits(int $visits): self
    {
        $this->visits = $visits;
        return $this;
    }

    public function getPagevisitsAuthorized(): array
    {
        $pagevisits = $this->pagevisits->toArray();
        foreach ($pagevisits as $key => $pagevisit) {
            /** @var Pagevisit $pagevisits */
            if ($pagevisit->canBeRead() === false) {
                unset($pagevisits[$key]);
            }
        }
        return $pagevisits;
    }

    /**
     * Get pagevisits of a visitor and sort it descending (last visit at first)
     *
     * @return array
     * @throws Exception
     */
    public function getPagevisits(): array
    {
        $pagevisits = $this->getPagevisitsAuthorized();
        $pagevisitsArray = [];
        /** @var Pagevisit $pagevisit */
        foreach ($pagevisits as $pagevisit) {
            $pagevisitsArray[$pagevisit->getCrdate()->getTimestamp()] = $pagevisit;
        }
        krsort($pagevisitsArray);
        return $pagevisitsArray;
    }

    public function getPagevisitsOfGivenPageIdentifier(int $pageIdentifier): array
    {
        $pagevisits = $this->getPagevisitsAuthorized();
        $pagevisitsArray = [];
        /** @var Pagevisit $pagevisit */
        foreach ($pagevisits as $pagevisit) {
            if ($pagevisit->getPage() !== null && $pagevisit->getPage()->getUid() === $pageIdentifier) {
                $pagevisitsArray[$pagevisit->getCrdate()->getTimestamp()] = $pagevisit;
            }
        }
        krsort($pagevisitsArray);
        return $pagevisitsArray;
    }

    public function getPagevisitLast(): ?Pagevisit
    {
        $pagevisits = $this->getPagevisits();
        foreach ($pagevisits as $pagevisit) {
            return $pagevisit;
        }
        return null;
    }

    public function getPagevisitFirst(): ?Pagevisit
    {
        $pagevisits = $this->getPagevisits();
        ksort($pagevisits);
        foreach ($pagevisits as $pagevisit) {
            return $pagevisit;
        }
        return null;
    }

    /**
     * Always return a date object
     *
     * @return DateTime
     * @throws Exception
     */
    public function getDateOfPagevisitFirst(): DateTime
    {
        $date = new DateTime('-30 days');
        if ($this->getPagevisitFirst() !== null) {
            $date = $this->getPagevisitFirst()->getCrdate();
        }
        return $date;
    }

    public function setPagevisits(ObjectStorage $pagevisits): self
    {
        $this->pagevisits = $pagevisits;
        return $this;
    }

    public function addPagevisit(Pagevisit $pagevisit): self
    {
        $this->pagevisits->attach($pagevisit);
        return $this;
    }

    public function removePagevisit(Pagevisit $pagevisit): self
    {
        $this->pagevisits->detach($pagevisit);
        return $this;
    }

    public function getLastPagevisit(): ?Pagevisit
    {
        static $lastPagevisit = null;
        if ($lastPagevisit === null) {
            $pagevisits = $this->getPagevisits();
            $lastPagevisit = null;
            foreach ($pagevisits as $pagevisit) {
                $lastPagevisit = $pagevisit;
                break;
            }
        }
        return $lastPagevisit;
    }

    /**
     * Calculate number of unique page visits. If user show a reaction after min. 1h we define it as new pagevisit.
     *
     * @return int
     * @throws Exception
     */
    public function getNumberOfUniquePagevisits(): int
    {
        $pagevisits = $this->getPagevisitsAuthorized();
        $number = 1;
        if (count($pagevisits) > 1) {
            /** @var DateTime $lastVisit **/
            $lastVisit = null;
            foreach ($pagevisits as $pagevisit) {
                if ($lastVisit !== null) {
                    /** @var Pagevisit $pagevisit */
                    $interval = $lastVisit->diff($pagevisit->getCrdate());
                    // if difference is greater then one hour
                    if ($interval->h > 0) {
                        $number++;
                    }
                }
                $lastVisit = $pagevisit->getCrdate();
            }
        }
        return $number;
    }

    public function getNewsvisits(): ObjectStorage
    {
        return $this->newsvisits;
    }

    public function setNewsvisits(ObjectStorage $newsvisits): self
    {
        $this->newsvisits = $newsvisits;
        return $this;
    }

    public function addNewsvisit(Newsvisit $newsvisits): self
    {
        $this->newsvisits->attach($newsvisits);
        return $this;
    }

    public function removeNewsvisit(Newsvisit $newsvisits): self
    {
        $this->newsvisits->detach($newsvisits);
        return $this;
    }

    public function getLinkclicks(): ObjectStorage
    {
        return $this->linkclicks;
    }

    public function setLinkclicks(ObjectStorage $linkclicks): self
    {
        $this->linkclicks = $linkclicks;
        return $this;
    }

    public function addLinkclick(Linkclick $linkclick): self
    {
        $this->linkclicks->attach($linkclick);
        return $this;
    }

    public function removeLinkclick(Linkclick $linkclick): self
    {
        $this->linkclicks->detach($linkclick);
        return $this;
    }

    public function getAttributes(): ObjectStorage
    {
        return $this->attributes;
    }

    public function setAttributes(ObjectStorage $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function addAttribute(Attribute $attribute): self
    {
        $this->attributes->attach($attribute);
        return $this;
    }

    public function removeAttribute(Attribute $attribute): self
    {
        $this->attributes->detach($attribute);
        return $this;
    }

    public function getImportantAttributes(): array
    {
        $attributes = $this->getAttributes();
        $importantAttributes = [];
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), self::IMPORTANT_ATTRIBUTES)) {
                $importantAttributes[] = $attribute;
            }
        }
        return $importantAttributes;
    }

    public function getUnimportantAttributes(): array
    {
        $attributes = $this->getAttributes();
        $unimportant = [];
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if (!in_array($attribute->getName(), self::IMPORTANT_ATTRIBUTES)) {
                $unimportant[] = $attribute;
            }
        }
        return $unimportant;
    }

    public function getAttributesFromFrontenduser(): array
    {
        $attributes = [];
        $disallowed = ['password', 'lockToDomain'];
        if ($this->frontenduser !== null) {
            /** @noinspection PhpInternalEntityUsedInspection */
            foreach ($this->frontenduser->_getProperties() as $name => $value) {
                if (!empty($value) && in_array($name, $disallowed) === false) {
                    if (is_string($value) || is_int($value)) {
                        $attributes[] = [
                            'name' => $name,
                            'value' => $value,
                        ];
                    }
                }
            }
        }
        return $attributes;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getIpinformations(): ObjectStorage
    {
        return $this->ipinformations;
    }

    public function getImportantIpinformations(): array
    {
        $important = [
            'org',
            'country',
            'city',
        ];
        $informations = $this->getIpinformations();
        $importantInfo = [];
        /** @var Ipinformation $information */
        foreach ($informations as $information) {
            if (in_array($information->getName(), $important)) {
                $importantInfo[] = $information;
            }
        }
        return $importantInfo;
    }

    public function setIpinformations(ObjectStorage $ipinformations): self
    {
        $this->ipinformations = $ipinformations;
        return $this;
    }

    public function addIpinformation(Ipinformation $ipinformation): self
    {
        $this->ipinformations->attach($ipinformation);
        return $this;
    }

    public function removeIpinformation(Ipinformation $ipinformation): self
    {
        $this->ipinformations->detach($ipinformation);
        return $this;
    }

    public function getDownloads(): ObjectStorage
    {
        return $this->downloads;
    }

    public function setDownloads(ObjectStorage $downloads): self
    {
        $this->downloads = $downloads;
        return $this;
    }

    public function addDownload(Download $download): self
    {
        $this->downloads->attach($download);
        return $this;
    }

    public function removeDownload(Download $download): self
    {
        $this->downloads->detach($download);
        return $this;
    }

    public function getLastDownload(): ?Download
    {
        $downloads = $this->getDownloads();
        $download = null;
        foreach ($downloads as $downloadItem) {
            /** @var Download $download */
            $download = $downloadItem;
        }
        return $download;
    }

    public function getLogs(): array
    {
        $logs = $this->logs->toArray();
        $logs = $this->filterLogsByAuthentication($logs);
        krsort($logs);
        return $logs;
    }

    protected function filterLogsByAuthentication(array $logs): array
    {
        foreach ($logs as $key => $log) {
            /** @var Log $log */
            if ($log->canBeRead() === false) {
                unset($logs[$key]);
            }
        }
        return $logs;
    }

    public function setLogs(ObjectStorage $logs): self
    {
        $this->logs = $logs;
        return $this;
    }

    public function addLog(Log $log): self
    {
        if ($this->logs !== null) {
            $this->logs->attach($log);
        }
        return $this;
    }

    public function removeLog(Log $log): self
    {
        $this->logs->detach($log);
        return $this;
    }

    public function getCrdate(): DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    public function getTstamp(): DateTime
    {
        return $this->tstamp;
    }

    /**
     * Field "tstamp" should not be used to get the latest page visit because there could be some passive tasks
     * that update visitor records but this does not mean that the visitor was on your page at this time
     *
     * @return ?DateTime
     */
    public function getDateOfLastVisit(): ?DateTime
    {
        $date = null;
        $pagevisit = $this->getLastPagevisit();
        if ($pagevisit !== null) {
            $date = $pagevisit->getCrdate();
        }
        return $date;
    }

    public function setTstamp(DateTime $tstamp): self
    {
        $this->tstamp = $tstamp;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isBlacklisted(): bool
    {
        return $this->blacklisted;
    }

    public function isNotBlacklisted(): bool
    {
        return !$this->isBlacklisted();
    }

    public function setBlacklisted(bool $blacklisted): self
    {
        $this->blacklisted = $blacklisted;
        return $this;
    }

    /**
     * Set blacklisted flag and remove all related records to this visitor.
     * In addition, clean all other visitor properties.
     *
     * @return void
     */
    public function setBlacklistedStatus(): void
    {
        $this->setScoring(0);
        $this->setVisits(0);
        $this->setIpAddress('');

        $this->attributes = new ObjectStorage();
        $this->categoryscorings = new ObjectStorage();
        $this->downloads = new ObjectStorage();
        $this->ipinformations = new ObjectStorage();
        $this->linkclicks = new ObjectStorage();
        $this->logs = new ObjectStorage();
        $this->newsvisits = new ObjectStorage();
        $this->pagevisits = new ObjectStorage();

        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitorRepository->removeRelatedTableRowsByVisitor($this);

        $now = new DateTime();
        $this->setDescription('Blacklisted (' . $now->format('Y-m-d H:i:s') . ')');
        $this->setBlacklisted(true);
    }

    public function getFrontenduser(): ?FrontendUser
    {
        return $this->frontenduser;
    }

    public function setFrontenduser(FrontendUser $frontenduser): self
    {
        $this->frontenduser = $frontenduser;
        return $this;
    }

    /**
     * Search in database for a fe_users record with the same email and add a relation to it
     *
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    public function setFrontenduserAutomatically(): bool
    {
        if ($this->isIdentified() && $this->frontenduser === null) {
            $configurationService = ObjectUtility::getConfigurationService();
            $enabled = $configurationService->getTypoScriptSettingsByPath(
                'tracking.pagevisits.autoconnectToFeUsers'
            ) === '1';
            if ($enabled) {
                $feuRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
                $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
                $querySettings->setRespectStoragePage(false);
                $feuRepository->setDefaultQuerySettings($querySettings);
                /** @var FrontendUser|null $feuser */
                $feuser = $feuRepository->findOneByEmail($this->getEmail());
                if ($feuser !== null) {
                    $this->setFrontenduser($feuser);
                    return true;
                }
            }
        }
        return false;
    }

    public function getImageUrl(): string
    {
        $visitorImageService = GeneralUtility::makeInstance(VisitorImageService::class);
        return $visitorImageService->getUrl(['visitor' => $this]);
    }

    /**
     * Default: "Lastname, Firstname"
     * If empty, use: "email@company.org"
     * If still empty: "anonym"
     *
     * @return string
     */
    public function getFullName(): string
    {
        $name = $this->getNameCombination();
        if ($this->isIdentified()) {
            if (empty($name)) {
                $name = $this->getEmail();
            }
        } else {
            if (!empty($name)) {
                $name .= ' [' . LocalizationUtility::translateByKey('notIdentified') . ']';
            } else {
                $name = LocalizationUtility::translateByKey('anonym') . $this->getAnonymousPostfix();
            }
        }
        return $name;
    }

    public function getAnonymousPostfix(): string
    {
        return ' [' . StringUtility::shortMd5((string)$this->getUid()) . ']';
    }

    /**
     * Always show email address. If name exists, also show a name
     *
     * @return string "Muster, Max (max.muster@domain.org)"
     */
    public function getFullNameWithEmail(): string
    {
        $name = $this->getNameCombination();
        if ($this->isIdentified()) {
            if ($name === '') {
                $name = $this->getEmail();
            } else {
                $name .= ' (' . $this->getEmail() . ')';
            }
        }
        return $name;
    }

    /**
     * @return string "Muster, Max"
     */
    protected function getNameCombination(): string
    {
        $firstname = $this->getPropertyFromAttributes('firstname');
        $lastname = $this->getPropertyFromAttributes('lastname');
        $name = '';
        if (!empty($lastname)) {
            $name .= $lastname;
            if (!empty($firstname)) {
                $name .= ', ';
            }
        }
        if (!empty($firstname)) {
            $name .= $firstname;
        }
        return $name;
    }

    public function getLocation(): string
    {
        $country = $this->getCountry();
        $city = $this->getCity();
        $location = '';
        if (!empty($city)) {
            $location .= $city;
        }
        if (!empty($country)) {
            if (!empty($city)) {
                $location .= ' / ';
            }
            $location .= $country;
        }
        return $location;
    }

    public function getPhone(): string
    {
        return $this->getPropertyFromAttributes('phone');
    }

    public function getCountry(): string
    {
        $country = $this->getPropertyFromAttributes('country');
        if (empty($country)) {
            $country = $this->getPropertyFromIpinformations('country');
        }
        if (empty($country)) {
            $country = $this->getPropertyFromIpinformations('countryCode');
        }
        return $country;
    }

    public function getCity(): string
    {
        return $this->getPropertyFromIpinformations('city');
    }

    public function getPropertyFromAttributes(string $key): string
    {
        $attributes = $this->getAttributes();
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === $key) {
                return $attribute->getValue();
            }
        }
        return '';
    }

    public function getPropertyFromIpinformations(string $key): string
    {
        $ipinformations = $this->getIpinformations();
        if ($ipinformations->count() > 0) {
            /** @var Ipinformation $ipinformation */
            foreach ($ipinformations as $ipinformation) {
                if ($ipinformation->getName() === $key) {
                    return $ipinformation->getValue();
                }
            }
        }
        return '';
    }

    /**
     * Try to find any property by name. First look into direct getters of this model, then search in attributes and
     * then search in ipinformations.
     *
     * @param string $key
     * @return mixed
     */
    public function getAnyPropertyByName(string $key)
    {
        if (method_exists($this, 'get' . ucfirst($key))) {
            return $this->{'get' . ucfirst($key)}();
        }
        if (method_exists($this, 'is' . ucfirst($key))) {
            return $this->{'is' . ucfirst($key)};
        }
        $fromAttributes = $this->getPropertyFromAttributes($key);
        if ($fromAttributes !== '') {
            return $fromAttributes;
        }
        $fromIpinformations = $this->getPropertyFromIpinformations($key);
        if ($fromIpinformations !== '') {
            return $fromIpinformations;
        }
        return '';
    }

    public function getLatitude(): string
    {
        $lat = '';
        $ipInformations = $this->getIpinformations();
        foreach ($ipInformations as $information) {
            if ($information->getName() === 'lat') {
                $lat = $information->getValue();
            }
        }
        return $lat;
    }

    public function getLongitude(): string
    {
        $lng = '';
        $ipInformations = $this->getIpinformations();
        foreach ($ipInformations as $information) {
            if ($information->getName() === 'lon') {
                $lng = $information->getValue();
            }
        }
        return $lng;
    }

    public function getLatestAbtesting(): ?Abpagevisit
    {
        try {
            return GeneralUtility::makeInstance(AbpagevisitRepository::class)->findLatestByVisitor($this);
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * Check if this record can be viewed by current editor
     *
     * @return bool
     */
    public function canBeRead(): bool
    {
        if (EnvironmentUtility::isBackend() === false || BackendUtility::isAdministrator()) {
            return true;
        }
        $sites = GeneralUtility::makeInstance(SiteService::class)->getAllowedSites();
        return GeneralUtility::makeInstance(VisitorRepository::class)
            ->canVisitorBeReadBySites($this, array_keys($sites));
    }

    /**
     * Sort all categoryscorings by scoring desc
     *
     * @param Categoryscoring $cs1
     * @param Categoryscoring $cs2
     * @return int
     */
    protected function sortForHottestCategoryscoring(Categoryscoring $cs1, Categoryscoring $cs2): int
    {
        $result = 0;
        if ($cs1->getScoring() > $cs2->getScoring()) {
            $result = -1;
        } elseif ($cs1->getScoring() < $cs2->getScoring()) {
            $result = 1;
        }
        return $result;
    }

    protected function getCategoryscoringsSortedByScoringCallback(Categoryscoring $cs1, Categoryscoring $cs2): int
    {
        return ($cs1->getScoring() < $cs2->getScoring()) ? 1 : -1;
    }
}

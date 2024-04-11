<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use Doctrine\DBAL\DBALException;
use Exception;
use In2code\Lux\Domain\Repository\CategoryscoringRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\GetCompanyFromIpService;
use In2code\Lux\Domain\Service\Provider\Telecommunication;
use In2code\Lux\Domain\Service\ScoringService;
use In2code\Lux\Domain\Service\VisitorImageService;
use In2code\Lux\Exception\FileNotFoundException;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Visitor
 */
class Visitor extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_visitor';
    const IMPORTANT_ATTRIBUTES = [
        'email',
        'firstname',
        'lastname',
        'company',
        'username',
    ];

    /**
     * @var int
     */
    protected $scoring = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Categoryscoring>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $categoryscorings = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Fingerprint>
     */
    protected $fingerprints = null;

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $company = '';

    /**
     * @var bool
     */
    protected $identified = false;

    /**
     * @var int
     */
    protected $visits = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Pagevisit>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $pagevisits = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Newsvisit>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $newsvisits = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Linkclick>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $linkclicks = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Attribute>
     */
    protected $attributes = null;

    /**
     * @var string
     */
    protected $ipAddress = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Ipinformation>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $ipinformations = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Download>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $downloads = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Log>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $logs = null;

    /**
     * @var DateTime
     */
    protected $crdate = null;

    /**
     * @var DateTime
     */
    protected $tstamp = null;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var bool
     */
    protected $blacklisted = false;

    /**
     * @var FrontendUser
     */
    protected $frontenduser = null;

    /**
     * Visitor constructor.
     */
    public function __construct()
    {
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

    /**
     * @return int
     */
    public function getScoring(): int
    {
        return $this->scoring;
    }

    /**
     * @param int $scoring
     * @return Visitor
     */
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

    /**
     * @return ObjectStorage
     */
    public function getCategoryscorings(): ObjectStorage
    {
        return $this->categoryscorings;
    }

    /**
     * @var ObjectStorage $categoryscorings
     * @return Visitor
     */
    public function setCategoryscorings(ObjectStorage $categoryscorings): self
    {
        $this->categoryscorings = $categoryscorings;
        return $this;
    }

    /**
     * @param Categoryscoring $categoryscoring
     * @return $this
     */
    public function addCategoryscoring(Categoryscoring $categoryscoring): self
    {
        $this->categoryscorings->attach($categoryscoring);
        return $this;
    }

    /**
     * @param Categoryscoring $categoryscoring
     * @return $this
     */
    public function removeCategoryscoring(Categoryscoring $categoryscoring): self
    {
        $this->categoryscorings->detach($categoryscoring);
        return $this;
    }

    /**
     * @return array
     */
    public function getCategoryscoringsSortedByScoring(): array
    {
        $categoryscorings = $this->getCategoryscorings()->toArray();
        usort($categoryscorings, [$this, 'getCategoryscoringsSortedByScoringCallback']);
        return $categoryscorings;
    }

    /**
     * @param Category $category
     * @return Categoryscoring|null
     */
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

    /**
     * @return Categoryscoring|null
     */
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

    /**
     * @param int $scoring
     * @param Category $category
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
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

    /**
     * @param int $value
     * @param Category $category
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function increaseCategoryscoringByCategory(int $value, Category $category): void
    {
        $scoring = 0;
        if ($this->getCategoryscoringByCategory($category) !== null) {
            $scoring = $this->getCategoryscoringByCategory($category)->getScoring();
        }
        $newScoring = $scoring + $value;
        $this->setCategoryscoringByCategory($newScoring, $category);
    }

    /**
     * @return ObjectStorage
     */
    public function getFingerprints(): ?ObjectStorage
    {
        return $this->fingerprints;
    }

    /**
     * Get related fingerprints sorted with the latest first
     *
     * @return array
     */
    public function getFingerprintsSorted(): array
    {
        $fingerprints = $this->getFingerprints()->toArray();
        return array_reverse($fingerprints);
    }

    /**
     * @return Fingerprint|null
     */
    public function getLatestFingerprint(): ?Fingerprint
    {
        $fingerprints = $this->getFingerprints();
        foreach ($fingerprints as $fingerprint) {
            return $fingerprint;
        }
        return null;
    }

    /**
     * @return array
     */
    public function getFingerprintValues(): array
    {
        $values = [];
        foreach ($this->getFingerprints() as $fingerprint) {
            $values[] = $fingerprint->getValue();
        }
        return $values;
    }

    /**
     * @var ObjectStorage $fingerprints
     * @return Visitor
     */
    public function setFingerprints(ObjectStorage $fingerprints): self
    {
        $this->fingerprints = $fingerprints;
        return $this;
    }

    /**
     * @param Fingerprint $fingerprint
     * @return $this
     */
    public function addFingerprint(Fingerprint $fingerprint): self
    {
        $this->fingerprints->attach($fingerprint);
        return $this;
    }

    /**
     * @param ObjectStorage $fingerprints
     * @return $this
     */
    public function addFingerprints(ObjectStorage $fingerprints): self
    {
        foreach ($fingerprints as $fingerprint) {
            /** @var Fingerprint $fingerprint */
            $this->addFingerprint($fingerprint);
        }
        return $this;
    }

    /**
     * @param Fingerprint $fingerprint
     * @return $this
     */
    public function removeFingerprint(Fingerprint $fingerprint): self
    {
        $this->fingerprints->detach($fingerprint);
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Visitor
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     * @throws InvalidConfigurationTypeException
     * @throws FileNotFoundException
     */
    public function getCompany(): string
    {
        $company = $this->company;
        if (empty($company)) {
            $company = $this->getPropertyFromAttributes('company');
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

    /**
     * @param string $company
     * @return Visitor
     */
    public function setCompany(string $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Set company from IP information if possible (on visitor creation)
     *
     * @return $this
     * @throws FileNotFoundException
     * @throws InvalidConfigurationTypeException
     */
    public function setCompanyAutomatic(): self
    {
        $company = $this->getCompany();
        if ($company !== '') {
            $this->company = $company;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentified(): bool
    {
        return $this->identified;
    }

    /**
     * @param bool $identified
     * @return Visitor
     */
    public function setIdentified(bool $identified): self
    {
        $this->identified = $identified;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisits(): int
    {
        return $this->visits;
    }

    /**
     * @param int $visits
     * @return Visitor
     */
    public function setVisits(int $visits): self
    {
        $this->visits = $visits;
        return $this;
    }

    /**
     * Get pagevisits of a visitor and sort it descending (last visit at first)
     *
     * @return array
     * @throws Exception
     */
    public function getPagevisits(): array
    {
        $pagevisits = $this->pagevisits;
        $pagevisitsArray = [];
        /** @var Pagevisit $pagevisit */
        foreach ($pagevisits as $pagevisit) {
            $pagevisitsArray[$pagevisit->getCrdate()->getTimestamp()] = $pagevisit;
        }
        krsort($pagevisitsArray);
        return $pagevisitsArray;
    }

    /**
     * @param int $pageIdentifier
     * @return array
     * @throws Exception
     */
    public function getPagevisitsOfGivenPageIdentifier(int $pageIdentifier): array
    {
        $pagevisits = $this->pagevisits;
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

    /**
     * Get last page visit
     *
     * @return Pagevisit|null
     * @throws Exception
     */
    public function getPagevisitLast(): ?Pagevisit
    {
        $pagevisits = $this->getPagevisits();
        foreach ($pagevisits as $pagevisit) {
            return $pagevisit;
        }
        return null;
    }

    /**
     * Get first page visit
     *
     * @return Pagevisit|null
     * @throws Exception
     */
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
     * @var ObjectStorage $pagevisits
     * @return Visitor
     */
    public function setPagevisits(ObjectStorage $pagevisits): self
    {
        $this->pagevisits = $pagevisits;
        return $this;
    }

    /**
     * @param Pagevisit $pagevisit
     * @return $this
     */
    public function addPagevisit(Pagevisit $pagevisit): self
    {
        $this->pagevisits->attach($pagevisit);
        return $this;
    }

    /**
     * @param Pagevisit $pagevisit
     * @return $this
     */
    public function removePagevisit(Pagevisit $pagevisit): self
    {
        $this->pagevisits->detach($pagevisit);
        return $this;
    }

    /**
     * @return Pagevisit|null
     * @throws Exception
     */
    public function getLastPagevisit(): ?Pagevisit
    {
        $pagevisits = $this->getPagevisits();
        $lastPagevisit = null;
        foreach ($pagevisits as $pagevisit) {
            $lastPagevisit = $pagevisit;
            break;
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
        $pagevisits = $this->pagevisits;
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

    /**
     * @return ObjectStorage
     * @noinspection PhpUnused
     */
    public function getNewsvisits(): ObjectStorage
    {
        return $this->newsvisits;
    }

    /**
     * @param ObjectStorage $newsvisits
     * @return Visitor
     * @noinspection PhpUnused
     */
    public function setNewsvisits(ObjectStorage $newsvisits): self
    {
        $this->newsvisits = $newsvisits;
        return $this;
    }

    /**
     * @param Newsvisit $newsvisits
     * @return $this
     */
    public function addNewsvisit(Newsvisit $newsvisits): self
    {
        $this->newsvisits->attach($newsvisits);
        return $this;
    }

    /**
     * @param Newsvisit $newsvisits
     * @return $this
     * @noinspection PhpUnused
     */
    public function removeNewsvisit(Newsvisit $newsvisits): self
    {
        $this->pagevisits->detach($newsvisits);
        return $this;
    }

    /**
     * @return ObjectStorage
     * @noinspection PhpUnused
     */
    public function getLinkclicks(): ObjectStorage
    {
        return $this->linkclicks;
    }

    /**
     * @param ObjectStorage $linkclicks
     * @return Visitor
     * @noinspection PhpUnused
     */
    public function setLinkclicks(ObjectStorage $linkclicks): self
    {
        $this->linkclicks = $linkclicks;
        return $this;
    }

    /**
     * @param Linkclick $linkclick
     * @return $this
     * @noinspection PhpUnused
     */
    public function addLinkclick(Linkclick $linkclick): self
    {
        $this->linkclicks->attach($linkclick);
        return $this;
    }

    /**
     * @param Linkclick $linkclick
     * @return $this
     * @noinspection PhpUnused
     */
    public function removeLinkclick(Linkclick $linkclick): self
    {
        $this->linkclicks->detach($linkclick);
        return $this;
    }

    /**
     * @return ObjectStorage
     */
    public function getAttributes(): ObjectStorage
    {
        return $this->attributes;
    }

    /**
     * @var ObjectStorage $attributes
     * @return Visitor
     */
    public function setAttributes(ObjectStorage $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function addAttribute(Attribute $attribute): self
    {
        $this->attributes->attach($attribute);
        return $this;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function removeAttribute(Attribute $attribute): self
    {
        $this->attributes->detach($attribute);
        return $this;
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return Visitor
     */
    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return ObjectStorage
     */
    public function getIpinformations(): ObjectStorage
    {
        return $this->ipinformations;
    }

    /**
     * @return array
     */
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

    /**
     * @var ObjectStorage $ipinformations
     * @return Visitor
     */
    public function setIpinformations(ObjectStorage $ipinformations): self
    {
        $this->ipinformations = $ipinformations;
        return $this;
    }

    /**
     * @param Ipinformation $ipinformation
     * @return $this
     */
    public function addIpinformation(Ipinformation $ipinformation): self
    {
        $this->ipinformations->attach($ipinformation);
        return $this;
    }

    /**
     * @param Ipinformation $ipinformation
     * @return $this
     * @noinspection PhpUnused
     */
    public function removeIpinformation(Ipinformation $ipinformation): self
    {
        $this->ipinformations->detach($ipinformation);
        return $this;
    }

    /**
     * @return ObjectStorage
     */
    public function getDownloads(): ObjectStorage
    {
        return $this->downloads;
    }

    /**
     * @param ObjectStorage $downloads
     * @return Visitor
     */
    public function setDownloads(ObjectStorage $downloads): self
    {
        $this->downloads = $downloads;
        return $this;
    }

    /**
     * @param Download $download
     * @return Visitor
     */
    public function addDownload(Download $download): self
    {
        $this->downloads->attach($download);
        return $this;
    }

    /**
     * @param Download $download
     * @return Visitor
     * @noinspection PhpUnused
     */
    public function removeDownload(Download $download): self
    {
        $this->downloads->detach($download);
        return $this;
    }

    /**
     * @return Download|null
     */
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

    /**
     * @return array
     */
    public function getLogs(): array
    {
        $logs = $this->logs->toArray();
        krsort($logs);
        return $logs;
    }

    /**
     * @return Log|null
     */
    public function getLatestLog(): ?Log
    {
        $logs = $this->getLogs();
        $values = array_values($logs);
        if (array_key_exists(0, $values)) {
            return $values[0];
        }
        return null;
    }

    /**
     * @var ObjectStorage $logs
     * @return Visitor
     */
    public function setLogs(ObjectStorage $logs): self
    {
        $this->logs = $logs;
        return $this;
    }

    /**
     * @param Log $log
     * @return $this
     */
    public function addLog(Log $log): self
    {
        if ($this->logs !== null) {
            $this->logs->attach($log);
        }
        return $this;
    }

    /**
     * @param Log $log
     * @return $this
     */
    public function removeLog(Log $log): self
    {
        $this->logs->detach($log);
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCrdate(): DateTime
    {
        return $this->crdate;
    }

    /**
     * @param DateTime $crdate
     * @return Visitor
     */
    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return DateTime
     * @noinspection PhpUnused
     */
    public function getTstamp(): DateTime
    {
        return $this->tstamp;
    }

    /**
     * You should not use "tstamp" to get the latest page visit because there could be some tasks that update visitor
     * records but this does not mean that the visitor was on your page at this time
     *
     * @return DateTime|null
     * @noinspection PhpUnused
     */
    public function getDateOfLastVisit(): ?DateTime
    {
        $log = $this->getLatestLog();
        if ($log !== null) {
            return $log->getCrdate();
        }
        return null;
    }

    /**
     * @param DateTime $tstamp
     * @return Visitor
     */
    public function setTstamp(DateTime $tstamp): self
    {
        $this->tstamp = $tstamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Visitor
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBlacklisted(): bool
    {
        return $this->blacklisted;
    }

    /**
     * @return bool
     */
    public function isNotBlacklisted(): bool
    {
        return !$this->isBlacklisted();
    }

    /**
     * @param bool $blacklisted
     * @return Visitor
     */
    public function setBlacklisted(bool $blacklisted): self
    {
        $this->blacklisted = $blacklisted;
        return $this;
    }

    /**
     * Set blacklisted flag and remove all related records to this visitor.
     * In addition clean all other visitor properties.
     *
     * @return void
     * @throws DBALException
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function setBlacklistedStatus(): void
    {
        $this->setScoring(0);
        $this->setEmail('');
        $this->setIdentified(false);
        $this->setVisits(0);
        $this->setIpAddress('');

        $this->categoryscorings = new ObjectStorage();
        $this->pagevisits = new ObjectStorage();
        $this->attributes = new ObjectStorage();
        $this->ipinformations = new ObjectStorage();
        $this->downloads = new ObjectStorage();
        $this->logs = new ObjectStorage();

        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitorRepository->removeRelatedTableRowsByVisitor($this);

        $now = new DateTime();
        $this->setDescription('Blacklisted (' . $now->format('Y-m-d H:i:s') . ')');
        $this->setBlacklisted(true);
    }

    /**
     * @return FrontendUser
     */
    public function getFrontenduser(): ?FrontendUser
    {
        return $this->frontenduser;
    }

    /**
     * @param FrontendUser $frontenduser
     * @return Visitor
     */
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
                /** @var FrontendUserRepository $feuRepository */
                $feuRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
                $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
                $querySettings->setRespectStoragePage(false);
                $feuRepository->setDefaultQuerySettings($querySettings);
                /** @var FrontendUser|null $feuser */
                /** @noinspection PhpUndefinedMethodInspection */
                $feuser = $feuRepository->findOneByEmail($this->getEmail());
                if ($feuser !== null) {
                    $this->setFrontenduser($feuser);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getImageUrl(): string
    {
        $visitorImageService = GeneralUtility::makeInstance(VisitorImageService::class, $this);
        return $visitorImageService->getUrl();
    }

    /**
     * Default: "Lastname, Firstname"
     * If empty, use: "email@company.org"
     * If still empty: "unknown"
     *
     * @return string
     */
    public function getFullName(): string
    {
        if ($this->isIdentified()) {
            $name = $this->getNameCombination();
            if (empty($name)) {
                $name = $this->getEmail();
            }
        } else {
            $name = $this->getNameCombination();
            if (!empty($name)) {
                $name .= ' [' . LocalizationUtility::translateByKey('notIdentified') . ']';
            } else {
                $name = LocalizationUtility::translateByKey('anonym');
            }
        }
        return $name;
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

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->getPropertyFromAttributes('phone');
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->getPropertyFromIpinformations('city');
    }

    /**
     * @param string $key
     * @return string
     */
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

    /**
     * @param string $key
     * @return string
     */
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

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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

    /**
     * @param Categoryscoring $a
     * @param Categoryscoring $b
     * @return int
     */
    protected function getCategoryscoringsSortedByScoringCallback(Categoryscoring $a, Categoryscoring $b): int
    {
        return ($a->getScoring() < $b->getScoring()) ? 1 : -1;
    }
}

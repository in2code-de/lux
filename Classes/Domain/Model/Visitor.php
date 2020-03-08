<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\CategoryscoringRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\ReadableReferrerService;
use In2code\Lux\Domain\Service\ScoringService;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Visitor
 */
class Visitor extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_visitor';
    const IMPORTANT_ATTRIBUTES = [
        'email',
        'firstname',
        'lastname',
        'company',
        'username'
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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Attribute>
     */
    protected $attributes = null;

    /**
     * @var string
     */
    protected $referrer = '';

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
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * @var \DateTime
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
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
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
    public function setScoring(int $scoring)
    {
        $this->scoring = $scoring;
        return $this;
    }

    /**
     * Get the scoring to any given time in the past
     *
     * @param \DateTime $time
     * @return int
     * @throws InvalidQueryException
     */
    public function getScoringByDate(\DateTime $time): int
    {
        $scoringService = ObjectUtility::getObjectManager()->get(ScoringService::class, $time);
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
    public function setCategoryscorings(ObjectStorage $categoryscorings)
    {
        $this->categoryscorings = $categoryscorings;
        return $this;
    }

    /**
     * @param Categoryscoring $categoryscoring
     * @return $this
     */
    public function addCategoryscoring(Categoryscoring $categoryscoring)
    {
        $this->categoryscorings->attach($categoryscoring);
        return $this;
    }

    /**
     * @param Categoryscoring $categoryscoring
     * @return $this
     */
    public function removeCategoryscoring(Categoryscoring $categoryscoring)
    {
        $this->categoryscorings->detach($categoryscoring);
        return $this;
    }

    /**
     * @return array
     */
    public function getCategoryscoringsSortedByScoring(): array
    {
        $categoryscoringsOs = $this->getCategoryscorings();
        $categoryscorings = [];
        /** @var Categoryscoring $categoryscoring */
        foreach ($categoryscoringsOs as $categoryscoring) {
            $categoryscorings[$categoryscoring->getScoring()] = $categoryscoring;
        }
        krsort($categoryscorings);
        return $categoryscorings;
    }

    /**
     * @param Category $category
     * @return Categoryscoring|null
     */
    public function getCategoryscoringByCategory(Category $category)
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
    public function getHottestCategoryscoring()
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
    public function setCategoryscoringByCategory(int $scoring, Category $category)
    {
        /** @var CategoryscoringRepository $csRepository */
        $csRepository = ObjectUtility::getObjectManager()->get(CategoryscoringRepository::class);
        $categoryscoring = $this->getCategoryscoringByCategory($category);
        if ($categoryscoring !== null) {
            $categoryscoring->setScoring($scoring);
            $csRepository->update($categoryscoring);
        } else {
            /** @var Categoryscoring $categoryscoring */
            $categoryscoring = ObjectUtility::getObjectManager()->get(Categoryscoring::class);
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
    public function increaseCategoryscoringByCategory(int $value, Category $category)
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
     * @var ObjectStorage $fingerprints
     * @return Visitor
     */
    public function setFingerprints(ObjectStorage $fingerprints)
    {
        $this->fingerprints = $fingerprints;
        return $this;
    }

    /**
     * @param Fingerprint $fingerprint
     * @return $this
     */
    public function addFingerprint(Fingerprint $fingerprint)
    {
        $this->fingerprints->attach($fingerprint);
        return $this;
    }

    /**
     * @param Fingerprint $fingerprint
     * @return $this
     */
    public function removeFingerprint(Fingerprint $fingerprint)
    {
        $this->fingerprints->detach($fingerprint);
        return $this;
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
     * @param ObjectStorage $fingerprints
     * @return $this
     */
    public function addFingerprints(ObjectStorage $fingerprints)
    {
        foreach ($fingerprints as $fingerprint) {
            /** @var Fingerprint $fingerprint */
            $this->addFingerprint($fingerprint);
        }
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
    public function setEmail(string $email)
    {
        $this->email = $email;
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
    public function setIdentified(bool $identified)
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
    public function setVisits(int $visits)
    {
        $this->visits = $visits;
        return $this;
    }

    /**
     * Get pagevisits of a visitor and sort it descending (last visit at first)
     *
     * @return array
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function getPagevisitLast()
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
     * @throws \Exception
     */
    public function getPagevisitFirst()
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
    public function setPagevisits(ObjectStorage $pagevisits)
    {
        $this->pagevisits = $pagevisits;
        return $this;
    }

    /**
     * @param Pagevisit $pagevisit
     * @return $this
     */
    public function addPagevisit(Pagevisit $pagevisit)
    {
        $this->pagevisits->attach($pagevisit);
        return $this;
    }

    /**
     * @param Pagevisit $pagevisit
     * @return $this
     */
    public function removePagevisit(Pagevisit $pagevisit)
    {
        $this->pagevisits->detach($pagevisit);
        return $this;
    }

    /**
     * @return Pagevisit|null
     * @throws \Exception
     */
    public function getLastPagevisit()
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
     * @throws \Exception
     */
    public function getNumberOfUniquePagevisits(): int
    {
        $pagevisits = $this->pagevisits;
        $number = 1;
        if (count($pagevisits) > 1) {
            /** @var \DateTime $lastVisit **/
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
     */
    public function getAttributes(): ObjectStorage
    {
        return $this->attributes;
    }

    /**
     * @var ObjectStorage $attributes
     * @return Visitor
     */
    public function setAttributes(ObjectStorage $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes->attach($attribute);
        return $this;
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function removeAttribute(Attribute $attribute)
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
            foreach ($this->frontenduser->_getProperties() as $name => $value) {
                if (!empty($value) && in_array($name, $disallowed) === false) {
                    if (is_string($value) || is_int($value)) {
                        $attributes[] = [
                            'name' => $name,
                            'value' => $value
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
    public function getReferrer(): string
    {
        return $this->referrer;
    }

    /**
     * @param string $referrer
     * @return Visitor
     */
    public function setReferrer(string $referrer)
    {
        $this->referrer = $referrer;
        return $this;
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
    public function setIpAddress(string $ipAddress)
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
            'city'
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
    public function setIpinformations(ObjectStorage $ipinformations)
    {
        $this->ipinformations = $ipinformations;
        return $this;
    }

    /**
     * @param Ipinformation $ipinformation
     * @return $this
     */
    public function addIpinformation(Ipinformation $ipinformation)
    {
        $this->ipinformations->attach($ipinformation);
        return $this;
    }

    /**
     * @param Ipinformation $ipinformation
     * @return $this
     */
    public function removeIpinformation(Ipinformation $ipinformation)
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
    public function setDownloads(ObjectStorage $downloads)
    {
        $this->downloads = $downloads;
        return $this;
    }

    /**
     * @param Download $download
     * @return Visitor
     */
    public function addDownload(Download $download): Visitor
    {
        $this->downloads->attach($download);
        return $this;
    }

    /**
     * @param Download $download
     * @return Visitor
     */
    public function removeDownload(Download $download): Visitor
    {
        $this->downloads->detach($download);
        return $this;
    }

    /**
     * @return Download|null
     */
    public function getLastDownload()
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
     * @var ObjectStorage $logs
     * @return Visitor
     */
    public function setLogs(ObjectStorage $logs)
    {
        $this->logs = $logs;
        return $this;
    }

    /**
     * @param Log $log
     * @return $this
     */
    public function addLog(Log $log)
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
    public function removeLog(Log $log)
    {
        $this->logs->detach($log);
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCrdate(): \DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return Visitor
     */
    public function setCrdate(\DateTime $crdate)
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTstamp(): \DateTime
    {
        return $this->tstamp;
    }

    /**
     * @param \DateTime $tstamp
     * @return Visitor
     */
    public function setTstamp(\DateTime $tstamp)
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
    public function setDescription(string $description)
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
    public function setBlacklisted(bool $blacklisted)
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
     */
    public function setBlacklistedStatus()
    {
        $this->setScoring(0);
        $this->setEmail('');
        $this->setIdentified(false);
        $this->setVisits(0);
        $this->setReferrer('');
        $this->setIpAddress('');

        $this->categoryscorings = null;
        $this->pagevisits = null;
        $this->attributes = null;
        $this->ipinformations = null;
        $this->downloads = null;
        $this->logs = null;

        /** @var VisitorRepository $visitorRepository */
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $visitorRepository->removeRelatedTableRowsByVisitorUid($this);

        /** @noinspection PhpUnhandledExceptionInspection */
        $now = new \DateTime();
        $this->setDescription('Blacklisted (' . $now->format('Y-m-d H:i:s') . ')');
        $this->setBlacklisted(true);
    }

    /**
     * @return FrontendUser
     */
    public function getFrontenduser()
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
     * Calculated properties
     */

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
     * @return string
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
    public function getCompany(): string
    {
        $company = $this->getPropertyFromAttributes('company');
        if (empty($company)) {
            $company = $this->getPropertyFromIpinformations('isp');
            if ($this->isTelecomProvider($company)) {
                $company = '';
            }
        }
        return $company;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->getPropertyFromIpinformations('country');
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
     * @return string
     */
    public function getReadableReferrer(): string
    {
        $referrerService = ObjectUtility::getObjectManager()->get(ReadableReferrerService::class, $this->getReferrer());
        return $referrerService->getReadableReferrer();
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
     * @param string $company
     * @return bool
     */
    protected function isTelecomProvider(string $company): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $tpFileList = $configurationService->getTypoScriptSettingsByPath('general.telecommunicationProviderList');
        return FileUtility::isStringInFile($company, GeneralUtility::getFileAbsFileName($tpFileList));
    }
}

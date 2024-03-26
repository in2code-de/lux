<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\SearchRepository;
use In2code\Lux\Domain\Repository\UtmRepository;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\BackendUtility;
use In2code\Luxenterprise\Domain\Model\AbTestingPage;
use In2code\Luxenterprise\Domain\Repository\AbTestingPageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Log extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_log';
    public const STATUS_DEFAULT = 0;
    public const STATUS_NEW = 1;
    public const STATUS_IDENTIFIED = 2; // Fieldlistening
    public const STATUS_IDENTIFIED_EMAIL4LINK = 21;
    public const STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAIL = 22;
    public const STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAILFAILED = 23;
    public const STATUS_IDENTIFIED_FORMLISTENING = 25;
    public const STATUS_IDENTIFIED_FRONTENDAUTHENTICATION = 26;
    public const STATUS_IDENTIFIED_LUXLETTERLINK = 28;
    public const STATUS_ATTRIBUTE = 3;
    public const STATUS_PAGEVISIT2 = 40;
    public const STATUS_PAGEVISIT3 = 41;
    public const STATUS_PAGEVISIT4 = 42;
    public const STATUS_PAGEVISIT5 = 43;
    public const STATUS_DOWNLOAD = 50;
    public const STATUS_SEARCH = 55;
    public const STATUS_ACTION = 60;
    public const STATUS_ACTION_QUEUED = 61;
    public const STATUS_CONTEXTUAL_CONTENT = 70;
    public const STATUS_LINKLISTENER = 80;
    public const STATUS_MERGE_BYFINGERPRINT = 90;
    public const STATUS_MERGE_BYEMAIL = 91;
    public const STATUS_SHORTENER_VISIT = 100;
    public const STATUS_ABTESTING_PAGE = 200;
    public const STATUS_UTM_TRACK = 300;
    public const STATUS_WIREDMINDS_CONNECTION = 400;
    public const STATUS_WIREDMINDS_SUCCESSFUL = 410;
    public const STATUS_API_CREATEVISITOR = 500;
    public const STATUS_ERROR = 900;

    protected int $status = self::STATUS_DEFAULT;

    protected ?Visitor $visitor = null;
    protected ?DateTime $crdate = null;
    protected string $properties = '';
    protected string $site = '';

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
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

    public function getProperties(): array
    {
        return (array)json_decode($this->properties, true);
    }

    public function setProperties(string $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function setPropertiesArray(array $properties): self
    {
        $this->properties = json_encode($properties);
        return $this;
    }

    public function getHref(): string
    {
        return ltrim($this->getPropertyByKey('href'), '/');
    }

    public function getWorkflowTitle(): string
    {
        return $this->getPropertyByKey('workflowTitle');
    }

    public function getActionTitle(): string
    {
        return $this->getPropertyByKey('actionTitle');
    }

    public function getActionExecutionTime(): int
    {
        return (int)$this->getPropertyByKey('executionTime');
    }

    public function getShownContentUid(): string
    {
        return $this->getPropertyByKey('shownContentUid');
    }

    public function getPageUid(): string
    {
        return $this->getPropertyByKey('pageUid');
    }

    public function getShortenerpath(): string
    {
        return $this->getPropertyByKey('path');
    }

    public function getAbTestingPage(): ?AbTestingPage
    {
        $abTestingPageIdentifier = (int)$this->getPropertyByKey('abTestingPage');
        if ($abTestingPageIdentifier > 0) {
            $abTestingPageRepository = GeneralUtility::makeInstance(AbTestingPageRepository::class);
            return $abTestingPageRepository->findByUid($abTestingPageIdentifier);
        }
        return null;
    }

    public function getUtm(): ?Utm
    {
        $utmIdentifier = (int)$this->getPropertyByKey('utm');
        if ($utmIdentifier > 0) {
            $utmRepository = GeneralUtility::makeInstance(UtmRepository::class);
            return $utmRepository->findByUid($utmIdentifier);
        }
        return null;
    }

    public function getSearch(): ?Search
    {
        $searchUid = (int)$this->getPropertyByKey('search');
        $searchRepository = GeneralUtility::makeInstance(SearchRepository::class);
        return $searchRepository->findByIdentifier($searchUid);
    }

    public function getLinklistener(): ?Linklistener
    {
        $linklistenerUid = (int)$this->getPropertyByKey('linklistener');
        $linklistener = GeneralUtility::makeInstance(LinklistenerRepository::class);
        return $linklistener->findByIdentifier($linklistenerUid);
    }

    protected function getPropertyByKey(string $key): string
    {
        $property = '';
        $properties = $this->getProperties();
        if (array_key_exists($key, $properties)) {
            $property = (string)$properties[$key];
        }
        return $property;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function setSite(string $site): self
    {
        $this->site = $site;
        return $this;
    }

    public static function getIdentifiedStatus(): array
    {
        return [
            Log::STATUS_IDENTIFIED,
            Log::STATUS_IDENTIFIED_FORMLISTENING,
            Log::STATUS_IDENTIFIED_LUXLETTERLINK,
            Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION,
            Log::STATUS_IDENTIFIED_EMAIL4LINK,
        ];
    }

    /**
     * Check if this record can be viewed by current editor
     *
     * @return bool
     */
    public function canBeRead(): bool
    {
        if (BackendUtility::isAdministrator() || $this->site === '') {
            return true;
        }
        $sites = GeneralUtility::makeInstance(SiteService::class)->getAllowedSites();
        return array_key_exists($this->getSite(), $sites);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Pagevisit extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_pagevisit';

    protected ?Visitor $visitor = null;
    protected ?Page $page = null;
    protected ?DateTime $crdate = null;

    protected string $referrer = '';
    protected string $domain = '';
    protected string $site = '';

    protected int $language = 0;

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    /**
     * Get the related pagetitle with a language code as postfix (if additional language) like "Management (en)"
     *
     * @return string
     * @throws SiteNotFoundException
     */
    public function getPageTitleWithLanguage(): string
    {
        $title = '';
        $page = $this->getPage();
        if ($page !== null) {
            $title = $page->getTitle();
            if ($this->getLanguage() > 0) {
                /** @var SiteService $siteService */
                $siteService = GeneralUtility::makeInstance(SiteService::class);
                $code = $siteService->getLanguageCodeFromLanguageAndPageIdentifier(
                    $this->getLanguage(),
                    $this->getPage()->getUid()
                );
                $title .= ' (' . $code . ')';
            }
            if ($this->getNewsvisit() !== null && $this->getNewsvisit()->getNews() !== null) {
                $title .= ' "' . $this->getNewsvisit()->getNews()->getTitle() . '"';
            }
        }
        return $title;
    }

    public function setPage(Page $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getLanguage(): int
    {
        return $this->language;
    }

    public function setLanguage(int $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getCrdate(): DateTime
    {
        $crdate = $this->crdate;
        if ($crdate === null) {
            $crdate = new DateTime();
        }
        return $crdate;
    }

    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function getReadableReferrer(): string
    {
        $referrerService = GeneralUtility::makeInstance(Readable::class, $this->getReferrer());
        return $referrerService->getReadableReferrer();
    }

    public function setReferrer(string $referrer): self
    {
        $this->referrer = $referrer;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
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

    public function setDomainAutomatically(): self
    {
        $this->domain = FrontendUtility::getCurrentDomain();
        return $this;
    }

    public function getAllPagevisits(): array
    {
        return $this->getVisitor()->getPagevisits();
    }

    public function getNextPagevisit(): ?Pagevisit
    {
        $allPagevisits = $this->getAllPagevisits();
        $thisFound = false;
        $nextPagevisit = null;
        foreach ($allPagevisits as $pagevisit) {
            if ($thisFound === true) {
                $nextPagevisit = $pagevisit;
                break;
            }
            if ($pagevisit === $this) {
                $thisFound = true;
            }
        }
        return $nextPagevisit;
    }

    public function getPreviousPagevisit(): ?Pagevisit
    {
        $allPagevisits = $this->getAllPagevisits();
        $previousPagevisit = $lastPagevisit = null;
        foreach ($allPagevisits as $pagevisit) {
            if ($pagevisit === $this) {
                $previousPagevisit = $lastPagevisit;
            }
            $lastPagevisit = $pagevisit;
        }
        return $previousPagevisit;
    }

    public function getNewsvisit(): ?Newsvisit
    {
        if (ExtensionManagementUtility::isLoaded('news') === false) {
            return null;
        }

        $newsvisitRepository = GeneralUtility::makeInstance(NewsvisitRepository::class);
        return $newsvisitRepository->findByPagevisit($this);
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

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Newsvisit extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_newsvisit';

    protected ?Visitor $visitor = null;
    protected ?News $news = null;
    protected ?DateTime $crdate = null;
    protected ?Pagevisit $pagevisit = null;

    protected string $domain = '';

    protected int $language = 0;

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }

    public function getNews(): ?News
    {
        return $this->news;
    }

    /**
     * Get the related newstitle with a language code as postfix (if additional language) like "Management (en)"
     *
     * @return string
     */
    public function getNewsTitleWithLanguage(): string
    {
        $title = '';
        $news = $this->getNews();
        if ($news !== null) {
            $title = $news->getTitle();
            if ($this->getLanguage() > 0) {
                /** @var SiteService $siteService */
                $siteService = GeneralUtility::makeInstance(SiteService::class);
                $code = $siteService->getLanguageCodeFromLanguageAndDomain($this->getLanguage(), $this->getDomain());
                if ($code !== '') {
                    $title .= ' (' . $code . ')';
                }
            }
        }
        return $title;
    }

    public function setNews(News $news): self
    {
        $this->news = $news;
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

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function setDomainAutomatically(): self
    {
        $this->domain = FrontendUtility::getCurrentDomain();
        return $this;
    }

    public function getPagevisit(): Pagevisit
    {
        return $this->pagevisit;
    }

    public function setPagevisit(Pagevisit $pagevisit = null): self
    {
        $this->pagevisit = $pagevisit;
        return $this;
    }
}

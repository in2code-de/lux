<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Newsvisit
 */
class Newsvisit extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_newsvisit';

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @var \In2code\Lux\Domain\Model\News
     */
    protected $news = null;

    /**
     * @var int
     */
    protected $language = 0;

    /**
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var \In2code\Lux\Domain\Model\Pagevisit
     */
    protected $pagevisit = null;

    /**
     * @return Visitor
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Newsvisit
     */
    public function setVisitor(Visitor $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return News|null
     */
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

    /**
     * @param News $news
     * @return Newsvisit
     */
    public function setNews(News $news): self
    {
        $this->news = $news;
        return $this;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @param int $language
     * @return Newsvisit
     */
    public function setLanguage(int $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getCrdate(): \DateTime
    {
        $crdate = $this->crdate;
        if ($crdate === null) {
            $crdate = new \DateTime();
        }
        return $crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return Newsvisit
     */
    public function setCrdate(\DateTime $crdate)
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return Newsvisit
     */
    public function setDomain(): self
    {
        $this->domain = FrontendUtility::getCurrentDomain();
        return $this;
    }

    /**
     * @return Pagevisit
     */
    public function getPagevisit(): Pagevisit
    {
        return $this->pagevisit;
    }

    /**
     * @param Pagevisit|null $pagevisit
     * @return Newsvisit
     */
    public function setPagevisit(Pagevisit $pagevisit = null): self
    {
        $this->pagevisit = $pagevisit;
        return $this;
    }
}

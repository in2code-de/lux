<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class Pagevisit
 */
class Pagevisit extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_pagevisit';

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @var \In2code\Lux\Domain\Model\Page
     */
    protected $page = null;

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
    protected $referrer = '';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @return Visitor
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Pagevisit
     */
    public function setVisitor(Visitor $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return Page
     */
    public function getPage()
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

    /**
     * @param Page $page
     * @return Pagevisit
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
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
     * @return Pagevisit
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
     * @return Pagevisit
     */
    public function setCrdate(\DateTime $crdate)
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

    /**
     * @return string
     * @throws Exception
     */
    public function getReadableReferrer(): string
    {
        $referrerService = GeneralUtility::makeInstance(Readable::class, $this->getReferrer());
        return $referrerService->getReadableReferrer();
    }

    /**
     * @param string $referrer
     * @return Pagevisit
     */
    public function setReferrer(string $referrer): self
    {
        $this->referrer = $referrer;
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
     * @return Pagevisit
     */
    public function setDomain(): self
    {
        $this->domain = FrontendUtility::getCurrentDomain();
        return $this;
    }

    /**
     * Get all pagevisits of the current visitor
     *
     * @return Pagevisit[]
     * @throws \Exception
     */
    public function getAllPagevisits()
    {
        return $this->getVisitor()->getPagevisits();
    }

    /**
     * @return Pagevisit|null
     * @throws \Exception
     */
    public function getNextPagevisit()
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

    /**
     * @return Pagevisit|null
     * @throws \Exception
     */
    public function getPreviousPagevisit()
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

    /**
     * @return Newsvisit|null
     */
    public function getNewsvisit(): ?Newsvisit
    {
        if (ExtensionManagementUtility::isLoaded('news') === false) {
            return null;
        }

        $newsvisitRepository = GeneralUtility::makeInstance(NewsvisitRepository::class);
        return $newsvisitRepository->findByPagevisit($this);
    }
}

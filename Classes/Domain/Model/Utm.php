<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Utm extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_utm';

    protected ?Pagevisit $pagevisit = null;
    protected ?Newsvisit $newsvisit = null;
    protected ?DateTime $crdate = null;

    protected string $utmSource = '';
    protected string $utmMedium = '';
    protected string $utmCampaign = '';
    protected string $utmId = '';
    protected string $utmTerm = '';
    protected string $utmContent = '';

    public function getPagevisit(): ?Pagevisit
    {
        return $this->pagevisit;
    }

    public function setPagevisit(?Pagevisit $pagevisit): Utm
    {
        $this->pagevisit = $pagevisit;
        return $this;
    }

    public function getNewsvisit(): ?Newsvisit
    {
        return $this->newsvisit;
    }

    public function setNewsvisit(?Newsvisit $newsvisit): Utm
    {
        $this->newsvisit = $newsvisit;
        return $this;
    }

    public function getUtmSource(): string
    {
        return $this->utmSource;
    }

    public function setUtmSource(string $utmSource): Utm
    {
        $this->utmSource = $utmSource;
        return $this;
    }

    public function getUtmMedium(): string
    {
        return $this->utmMedium;
    }

    public function setUtmMedium(string $utmMedium): Utm
    {
        $this->utmMedium = $utmMedium;
        return $this;
    }

    public function getUtmCampaign(): string
    {
        return $this->utmCampaign;
    }

    public function setUtmCampaign(string $utmCampaign): Utm
    {
        $this->utmCampaign = $utmCampaign;
        return $this;
    }

    public function getUtmId(): string
    {
        return $this->utmId;
    }

    public function setUtmId(string $utmId): Utm
    {
        $this->utmId = $utmId;
        return $this;
    }

    public function getUtmTerm(): string
    {
        return $this->utmTerm;
    }

    public function setUtmTerm(string $utmTerm): Utm
    {
        $this->utmTerm = $utmTerm;
        return $this;
    }

    public function getUtmContent(): string
    {
        return $this->utmContent;
    }

    public function setUtmContent(string $utmContent): Utm
    {
        $this->utmContent = $utmContent;
        return $this;
    }

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(?DateTime $crdate): Utm
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * Get visitor from pagevisit or from newsvisit
     *
     * @return ?Visitor
     */
    public function getVisitor(): ?Visitor
    {
        $visitor = null;
        if ($this->getPagevisit() !== null) {
            $visitor = $this->getPagevisit()->getVisitor();
        }
        if ($this->getNewsvisit() !== null) {
            $visitor = $this->getNewsvisit()->getVisitor();
        }
        return $visitor;
    }
}

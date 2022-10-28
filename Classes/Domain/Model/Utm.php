<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Utm
 */
class Utm extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_utm';

    /**
     * @var Pagevisit
     */
    protected $pagevisit = null;

    /**
     * @var Newsvisit
     */
    protected $newsvisit = null;

    /**
     * @var string
     */
    protected $utmSource = '';

    /**
     * @var string
     */
    protected $utmMedium = '';

    /**
     * @var string
     */
    protected $utmCampaign = '';

    /**
     * @var string
     */
    protected $utmId = '';

    /**
     * @var string
     */
    protected $utmTerm = '';

    /**
     * @var string
     */
    protected $utmContent = '';

    /**
     * @return Pagevisit
     */
    public function getPagevisit(): ?Pagevisit
    {
        return $this->pagevisit;
    }

    /**
     * @param ?Pagevisit $pagevisit
     * @return Utm
     */
    public function setPagevisit(?Pagevisit $pagevisit): Utm
    {
        $this->pagevisit = $pagevisit;
        return $this;
    }

    /**
     * @return Newsvisit
     */
    public function getNewsvisit(): ?Newsvisit
    {
        return $this->newsvisit;
    }

    /**
     * @param ?Newsvisit $newsvisit
     * @return Utm
     */
    public function setNewsvisit(?Newsvisit $newsvisit): Utm
    {
        $this->newsvisit = $newsvisit;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtmSource(): string
    {
        return $this->utmSource;
    }

    /**
     * @param string $utmSource
     * @return Utm
     */
    public function setUtmSource(string $utmSource): Utm
    {
        $this->utmSource = $utmSource;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtmMedium(): string
    {
        return $this->utmMedium;
    }

    /**
     * @param string $utmMedium
     * @return Utm
     */
    public function setUtmMedium(string $utmMedium): Utm
    {
        $this->utmMedium = $utmMedium;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtmCampaign(): string
    {
        return $this->utmCampaign;
    }

    /**
     * @param string $utmCampaign
     * @return Utm
     */
    public function setUtmCampaign(string $utmCampaign): Utm
    {
        $this->utmCampaign = $utmCampaign;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtmId(): string
    {
        return $this->utmId;
    }

    /**
     * @param string $utmId
     * @return Utm
     */
    public function setUtmId(string $utmId): Utm
    {
        $this->utmId = $utmId;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtmTerm(): string
    {
        return $this->utmTerm;
    }

    /**
     * @param string $utmTerm
     * @return Utm
     */
    public function setUtmTerm(string $utmTerm): Utm
    {
        $this->utmTerm = $utmTerm;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtmContent(): string
    {
        return $this->utmContent;
    }

    /**
     * @param string $utmContent
     * @return Utm
     */
    public function setUtmContent(string $utmContent): Utm
    {
        $this->utmContent = $utmContent;
        return $this;
    }
}

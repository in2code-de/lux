<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Company extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_company';

    protected string $title = '';
    protected string $branch = '';
    protected string $branchCode = '';
    protected string $city = '';
    protected string $contacts = '';
    protected string $continent = '';
    protected string $countryCode = '';
    protected string $region = '';
    protected string $street = '';
    protected string $zip = '';
    protected string $domain = '';
    protected string $foundingYear = '';
    protected string $phone = '';
    protected string $revenue = '';
    protected string $revenue_class = '';
    protected string $size = '';
    protected string $sizeClass = '';

    protected ?DateTime $crdate = null;
    protected ?DateTime $tstamp = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getBranch(): string
    {
        return $this->branch;
    }

    public function setBranch(string $branch): self
    {
        $this->branch = $branch;
        return $this;
    }

    public function getBranchCode(): string
    {
        return $this->branchCode;
    }

    public function setBranchCode(string $branchCode): self
    {
        $this->branchCode = $branchCode;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getContacts(): string
    {
        return $this->contacts;
    }

    public function setContacts(string $contacts): self
    {
        $this->contacts = $contacts;
        return $this;
    }

    public function getContinent(): string
    {
        return $this->continent;
    }

    public function setContinent(string $continent): self
    {
        $this->continent = $continent;
        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;
        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;
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

    public function getFoundingYear(): string
    {
        return $this->foundingYear;
    }

    public function setFoundingYear(string $foundingYear): self
    {
        $this->foundingYear = $foundingYear;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getRevenue(): string
    {
        return $this->revenue;
    }

    public function setRevenue(string $revenue): self
    {
        $this->revenue = $revenue;
        return $this;
    }

    public function getRevenueClass(): string
    {
        return $this->revenue_class;
    }

    public function setRevenueClass(string $revenue_class): self
    {
        $this->revenue_class = $revenue_class;
        return $this;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getSizeClass(): string
    {
        return $this->sizeClass;
    }

    public function setSizeClass(string $sizeClass): self
    {
        $this->sizeClass = $sizeClass;
        return $this;
    }

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(?DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    public function getTstamp(): ?DateTime
    {
        return $this->tstamp;
    }

    public function setTstamp(?DateTime $tstamp): self
    {
        $this->tstamp = $tstamp;
        return $this;
    }
}

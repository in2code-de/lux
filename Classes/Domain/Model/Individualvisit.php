<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;

class Individualvisit extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_individualvisit';

    protected ?Visitor $visitor = null;
    protected ?DateTime $crdate = null;
    protected ?Pagevisit $pagevisit = null;

    protected string $domain = '';
    protected string $tableForeign = '';

    protected int $identifierForeign = 0;
    protected int $language = 0;

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(?Visitor $visitor): self
    {
        $this->visitor = $visitor;
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

    public function getPagevisit(): ?Pagevisit
    {
        return $this->pagevisit;
    }

    public function setPagevisit(?Pagevisit $pagevisit): self
    {
        $this->pagevisit = $pagevisit;
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

    public function getTableForeign(): string
    {
        return $this->tableForeign;
    }

    public function setTableForeign(string $tableForeign): self
    {
        $this->tableForeign = $tableForeign;
        return $this;
    }

    public function getIdentifierForeign(): int
    {
        return $this->identifierForeign;
    }

    public function setIdentifierForeign(int $identifierForeign): self
    {
        $this->identifierForeign = $identifierForeign;
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
}

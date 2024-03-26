<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;

class Search extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_search';

    protected string $searchterm = '';

    protected ?Visitor $visitor = null;
    protected ?Pagevisit $pagevisit = null;
    protected ?DateTime $crdate = null;

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
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

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    public function getSearchterm(): string
    {
        return $this->searchterm;
    }

    public function setSearchterm(string $searchterm): self
    {
        $this->searchterm = $searchterm;
        return $this;
    }
}

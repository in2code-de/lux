<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;

class Linkclick extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_linkclick';

    protected ?DateTime $crdate = null;
    protected ?Linklistener $linklistener = null;
    protected ?Page $page = null;
    protected ?Visitor $visitor = null;

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    public function getLinklistener(): ?Linklistener
    {
        return $this->linklistener;
    }

    public function setLinklistener(Linklistener $linklistener): self
    {
        $this->linklistener = $linklistener;
        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(Page $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }
}

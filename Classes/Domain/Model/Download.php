<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use In2code\Lux\Utility\FrontendUtility;

class Download extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_download';

    protected string $href = '';
    protected string $domain = '';

    protected ?Visitor $visitor = null;
    protected ?DateTime $crdate = null;
    protected ?Page $page = null;
    protected ?File $file = null;

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
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

    public function getHref(): string
    {
        return $this->href;
    }

    public function setHref(string $href): self
    {
        $this->href = $href;
        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): Download
    {
        $this->page = $page;
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(): self
    {
        $this->domain = FrontendUtility::getCurrentDomain();
        return $this;
    }
}

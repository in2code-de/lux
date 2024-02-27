<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

class Categoryscoring extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_categoryscoring';

    protected int $scoring = 0;

    protected ?Category $category = null;
    protected ?Visitor $visitor = null;

    public function getScoring(): int
    {
        return $this->scoring;
    }

    public function setScoring(int $scoring): self
    {
        $this->scoring = $scoring;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

class Ipinformation extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_ipinformation';

    protected string $name = '';
    protected string $value = '';

    protected ?Visitor $visitor = null;

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }
}

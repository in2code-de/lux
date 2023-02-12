<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Utility\LocalizationUtility;

/**
 * Class Attribute
 */
class Attribute extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_attribute';
    const KEY_NAME = 'email';

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

    /**
     * Try to get a translation for the name, otherwise return name
     *
     * @return string
     */
    public function getLabel(): string
    {
        $label = LocalizationUtility::translateByKey('tx_lux_domain_model_attribute.label.' . $this->getName());
        if (empty($label)) {
            $label = $this->getName();
        }
        return $label;
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

    /**
     * Is this an attribute which contains the leads email address?
     *
     * @return bool
     */
    public function isEmail(): bool
    {
        return $this->getName() === self::KEY_NAME;
    }
}

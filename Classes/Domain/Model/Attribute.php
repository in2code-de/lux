<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Attribute
 */
class Attribute extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_attribute';
    const KEY_NAME = 'email';

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Attribute
     */
    public function setVisitor(Visitor $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return string
     */
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
        $lllPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
        $label = LocalizationUtility::translate($lllPrefix . 'tx_lux_domain_model_attribute.label.' . $this->getName());
        if (empty($label)) {
            $label = $this->getName();
        }
        return $label;
    }

    /**
     * @param string $name
     * @return Attribute
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Attribute
     */
    public function setValue(string $value)
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

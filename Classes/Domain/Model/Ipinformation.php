<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Model;

/**
 * Class Ipinformation
 */
class Ipinformation extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_ipinformation';

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
     * @return Ipinformation
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
     * @param string $name
     * @return Ipinformation
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
     * @return Ipinformation
     */
    public function setValue(string $value)
    {
        $this->value = $value;
        return $this;
    }
}

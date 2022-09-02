<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;

final class AttributeCreateEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @param Visitor $visitor
     * @param Attribute $attribute
     */
    public function __construct(Visitor $visitor, Attribute $attribute)
    {
        $this->visitor = $visitor;
        $this->attribute = $attribute;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Attribute
     */
    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}

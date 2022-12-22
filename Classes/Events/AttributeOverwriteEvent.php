<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;

final class AttributeOverwriteEvent
{
    protected Visitor $visitor;
    protected Attribute $attribute;

    public function __construct(Visitor $visitor, Attribute $attribute)
    {
        $this->visitor = $visitor;
        $this->attribute = $attribute;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}

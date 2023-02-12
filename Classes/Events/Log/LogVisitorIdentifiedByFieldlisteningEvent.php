<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;

final class LogVisitorIdentifiedByFieldlisteningEvent
{
    protected Visitor $visitor;
    protected ?Attribute $attribute = null;

    protected int $pageIdentifier = 0;

    public function __construct(Visitor $visitor, ?Attribute $attribute, int $pageIdentifier)
    {
        $this->visitor = $visitor;
        $this->attribute = $attribute;
        $this->pageIdentifier = $pageIdentifier;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function getPageIdentifier(): int
    {
        return $this->pageIdentifier;
    }
}

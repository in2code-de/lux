<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;

final class LogVisitorIdentifiedByEmail4linkEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Attribute|null
     */
    protected $attribute = null;

    /**
     * @var int
     */
    protected $pageIdentifier = 0;

    /**
     * @param Visitor $visitor
     * @param Attribute|null $attribute
     * @param int $pageIdentifier
     */
    public function __construct(Visitor $visitor, ?Attribute $attribute, int $pageIdentifier)
    {
        $this->visitor = $visitor;
        $this->attribute = $attribute;
        $this->pageIdentifier = $pageIdentifier;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Attribute|null
     */
    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    /**
     * @return int
     */
    public function getPageIdentifier(): int
    {
        return $this->pageIdentifier;
    }
}

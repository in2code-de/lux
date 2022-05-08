<?php
declare(strict_types = 1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Visitor;

final class LogVisitorEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }
}

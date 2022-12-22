<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Visitor;

final class SearchEvent
{
    protected Visitor $visitor;

    protected int $searchUid = 0;

    public function __construct(Visitor $visitor, int $searchUid)
    {
        $this->visitor = $visitor;
        $this->searchUid = $searchUid;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getSearchUid(): int
    {
        return $this->searchUid;
    }
}

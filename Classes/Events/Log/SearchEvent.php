<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Visitor;

final class SearchEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var int
     */
    protected $searchUid = 0;

    /**
     * @param Visitor $visitor
     * @param int $searchUid
     */
    public function __construct(Visitor $visitor, int $searchUid)
    {
        $this->visitor = $visitor;
        $this->searchUid = $searchUid;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return int
     */
    public function getSearchUid(): int
    {
        return $this->searchUid;
    }
}

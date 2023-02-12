<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Visitor;

final class LinkClickEvent
{
    protected Visitor $visitor;
    protected Linklistener $linklistener;

    protected int $pageUid = 0;

    public function __construct(Visitor $visitor, Linklistener $linklistener, int $pageUid)
    {
        $this->visitor = $visitor;
        $this->linklistener = $linklistener;
        $this->pageUid = $pageUid;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getLinklistener(): Linklistener
    {
        return $this->linklistener;
    }

    public function getPageUid(): int
    {
        return $this->pageUid;
    }
}

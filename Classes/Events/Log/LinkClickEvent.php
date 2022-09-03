<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Visitor;

final class LinkClickEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Linklistener
     */
    protected $linklistener;

    /**
     * @var int
     */
    protected $pageUid = 0;

    /**
     * @param Visitor $visitor
     * @param Linklistener $linklistener
     * @param int $pageUid
     */
    public function __construct(Visitor $visitor, Linklistener $linklistener, int $pageUid)
    {
        $this->visitor = $visitor;
        $this->linklistener = $linklistener;
        $this->pageUid = $pageUid;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Linklistener
     */
    public function getLinklistener(): Linklistener
    {
        return $this->linklistener;
    }

    /**
     * @return int
     */
    public function getPageUid(): int
    {
        return $this->pageUid;
    }
}

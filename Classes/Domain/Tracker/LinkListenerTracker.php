<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Signal\SignalTrait;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LinkListenerTracker logs link clicks with data-lux-linklistener="tagname"
 */
class LinkListenerTracker
{
    use SignalTrait;

    /**
     * @var Visitor|null
     */
    protected $visitor = null;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * DownloadTracker constructor.
     *
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @param string $tag
     * @param int $pageUid
     * @return void
     * @throws Exception
     */
    public function addLinkClick(string $tag, int $pageUid): void
    {
        $this->signalDispatch(__CLASS__, 'addLinkClick', [$tag, $pageUid, $this->visitor]);
    }
}

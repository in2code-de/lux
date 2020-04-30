<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Factory\LinkclickFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

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
     * @throws IllegalObjectTypeException
     */
    public function addLinkClick(string $tag, int $pageUid): void
    {
        $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickFactory::class, $this->visitor);
        $linkclick = $linkclickRepository->getAndPersist($tag, $pageUid);
        $this->signalDispatch(__CLASS__, 'addLinkClick', [$linkclick]);
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class LinkListenerTracker logs link clicks with data-lux-linklistener="tagname"
 */
class LinkListenerTracker
{
    use SignalTrait;

    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * @var LinkclickRepository
     */
    protected $linkclickRepository = null;

    /**
     * LinkListenerTracker constructor.
     * @param Visitor $visitor
     * @throws Exception
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
    }

    /**
     * @param int $linkclickIdentifier
     * @param int $pageUid
     * @return void
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function addLinkClick(int $linkclickIdentifier, int $pageUid): void
    {
        $linkclick = $this->linkclickRepository->findByIdentifier($linkclickIdentifier);
        $this->signalDispatch(__CLASS__, 'addLinkClick', [$this->visitor, $linkclick, $pageUid]);
    }
}

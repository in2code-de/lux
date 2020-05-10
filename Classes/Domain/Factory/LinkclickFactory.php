<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LinkclickFactory
 */
class LinkclickFactory
{
    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * LinkclickFactory constructor.
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @param string $tag
     * @param int $pageUid
     * @return Linkclick
     * @throws Exception
     * @throws IllegalObjectTypeException
     */
    public function getAndPersist(string $tag, int $pageUid): Linkclick
    {
        $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        $linkclick = $this->get($tag, $pageUid);
        $linkclickRepository->add($linkclick);
        return $linkclick;
    }

    /**
     * @param string $tag
     * @param int $pageUid
     * @return Linkclick
     * @throws Exception
     */
    public function get(string $tag, int $pageUid): Linkclick
    {
        $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
        /** @var Page $page */
        $page = $pageRepository->findByIdentifier($pageUid);

        $linkclick = ObjectUtility::getObjectManager()->get(Linkclick::class);
        $linkclick
            ->setCrdate(new \DateTime())
            ->setTag($tag)
            ->setPage($page)
            ->setVisitor($this->visitor);
        return $linkclick;
    }
}

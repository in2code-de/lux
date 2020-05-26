<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class Linklistener
 */
class Linklistener extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_linklistener';

    /**
     * @var \DateTime|null
     */
    protected $crdate = null;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var \In2code\Lux\Domain\Model\Category
     */
    protected $category = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Linkclick>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $linkclicks = null;

    /**
     * @return \DateTime|null
     */
    public function getCrdate(): ?\DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime|null $crdate
     * @return Linklistener
     */
    public function setCrdate(?\DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Linklistener
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return Linklistener
     */
    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return Linklistener
     */
    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getLinkclicks(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->linkclicks;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $linkclicks
     * @return Linklistener
     */
    public function setLinkclicks(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $linkclicks): self
    {
        $this->linkclicks = $linkclicks;
        return $this;
    }

    /**
     * Calculated properties
     */

    /**
     * @return array
     * @throws Exception
     */
    public function getLinkclicksRaw(): array
    {
        $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        return $linkclickRepository->findRawByLinklistenerIdentifier($this->getUid());
    }

    /**
     * @return float
     * @throws Exception
     */
    public function getPerformance(): float
    {
        if (count($this->getLinkclicksRaw()) === 0) {
            return 0.0;
        }
        $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        $groupedLinkclicks = $linkclickRepository->getAmountOfLinkclicksByLinklistenerGroupedByPageUid(
            $this->getUid()
        );
        $groupedLinkclicks = $this->extendGroupedLinkclicksWithDateAndPagevisits($groupedLinkclicks);
        $groupedLinkclicks = $this->combineMultipleGroupedLinkclicks($groupedLinkclicks);
        if ($groupedLinkclicks['pagevisits'] === 0) {
            return 0.0;
        }
        return $groupedLinkclicks['clickcount'] / $groupedLinkclicks['pagevisits'];
    }

    /**
     * @param array $groupedLinkclicks
     * @return array
     * @throws Exception
     */
    protected function extendGroupedLinkclicksWithDateAndPagevisits(array $groupedLinkclicks): array
    {
        $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
        $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
        foreach ($groupedLinkclicks as &$groupedLinkclick) {
            $groupedLinkclick['start'] = DateUtility::convertTimestamp($groupedLinkclick['crdate']);
            unset($groupedLinkclick['crdate']);
            $groupedLinkclick['end'] = $linkclickRepository->findLastDateByLinklistenerAndPage(
                $this->getUid(),
                $groupedLinkclick['page']
            );
            $filter = ObjectUtility::getFilterDtoFromStartAndEnd($groupedLinkclick['start'], $groupedLinkclick['end']);
            $groupedLinkclick['pagevisits'] = $pagevisitRepository->findAmountPerPage(
                $groupedLinkclick['page'],
                $filter
            );
        }
        return $groupedLinkclicks;
    }

    /**
     * @param array $groupedLinkclicks
     * @return array
     */
    protected function combineMultipleGroupedLinkclicks(array $groupedLinkclicks): array
    {
        foreach ($groupedLinkclicks as $key => $groupedLinkclick) {
            if ($key > 0) {
                $groupedLinkclicks[0]['clickcount'] += $groupedLinkclick['clickcount'];
                $groupedLinkclicks[0]['pagevisits'] += $groupedLinkclick['pagevisits'];
            }
        }
        unset($groupedLinkclicks[0]['page']);
        unset($groupedLinkclicks[0]['start']);
        unset($groupedLinkclicks[0]['end']);
        return $groupedLinkclicks[0];
    }
}

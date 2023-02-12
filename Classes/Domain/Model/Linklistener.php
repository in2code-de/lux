<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Linklistener extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_linklistener';

    protected ?DateTime $crdate = null;
    protected ?User $cruserId = null;
    protected ?Category $category = null;

    protected string $title = '';
    protected string $description = '';
    protected string $link = '';

    /**
     * @var ?ObjectStorage<Linkclick>
     * @Lazy
     */
    protected ?ObjectStorage $linkclicks = null;

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(?DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    public function getCruserId(): ?User
    {
        return $this->cruserId;
    }

    public function setCruserId(?User $cruserId): self
    {
        $this->cruserId = $cruserId;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getLinkclicks(): array
    {
        $linkclicks = $this->linkclicks;
        $linkclickArray = [];
        /** @var Linkclick $linkclick */
        foreach ($linkclicks as $linkclick) {
            $linkclickArray[$linkclick->getCrdate()->getTimestamp()] = $linkclick;
        }
        krsort($linkclickArray);
        return $linkclickArray;
    }

    public function setLinkclicks(ObjectStorage $linkclicks): self
    {
        $this->linkclicks = $linkclicks;
        return $this;
    }

    /**
     * Calculated properties
     */
    public function getLinkclicksRaw(): array
    {
        $linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
        return $linkclickRepository->findRawByLinklistenerIdentifier($this->getUid());
    }

    public function getPerformance(): float
    {
        if (count($this->getLinkclicksRaw()) === 0) {
            return 0.0;
        }
        $linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
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

    protected function extendGroupedLinkclicksWithDateAndPagevisits(array $groupedLinkclicks): array
    {
        $linkclickRepository = GeneralUtility::makeInstance(LinkclickRepository::class);
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
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

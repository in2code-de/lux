<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class News extends AbstractEntity
{
    const TABLE_NAME = 'tx_news_domain_model_news';

    protected ?DateTime $crdate = null;
    protected ?User $cruserId = null;

    protected string $title = '';

    /**
     * @var ?ObjectStorage<Category>
     */
    protected ?ObjectStorage $categories = null;

    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

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

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Return all identifiers to related categories
     *
     * @return array
     */
    public function getLuxCategories(): array
    {
        $categories = [];
        foreach ($this->getCategories() as $category) {
            if ($category->isLuxCategory() === true) {
                $categories[] = $category;
            }
        }
        return $categories;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class News
 */
class News extends AbstractEntity
{
    const TABLE_NAME = 'tx_news_domain_model_news';

    /**
     * @var \DateTime|null
     */
    protected $crdate = null;

    /**
     * @var \In2code\Lux\Domain\Model\User|null
     */
    protected $cruserId = null;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\In2code\Lux\Domain\Model\Category>
     */
    protected $categories = null;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

    /**
     * @return \DateTime|null
     */
    public function getCrdate(): ?\DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime|null $crdate
     * @return News
     */
    public function setCrdate(?\DateTime $crdate): News
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getCruserId(): ?User
    {
        return $this->cruserId;
    }

    /**
     * @param User|null $cruserId
     * @return News
     */
    public function setCruserId(?User $cruserId): News
    {
        $this->cruserId = $cruserId;
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
     * @return ObjectStorage
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Return all uids to related categories
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

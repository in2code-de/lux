<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Category
 */
class Category extends AbstractEntity
{
    const TABLE_NAME = 'sys_category';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var bool
     */
    protected $luxCategory = false;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Category
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLuxCategory(): bool
    {
        return $this->luxCategory;
    }

    /**
     * @param bool $luxCategory
     * @return Category
     */
    public function setLuxCategory(bool $luxCategory)
    {
        $this->luxCategory = $luxCategory;
        return $this;
    }
}

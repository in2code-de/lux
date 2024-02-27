<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Category extends AbstractEntity
{
    public const TABLE_NAME = 'sys_category';

    protected string $title = '';
    protected bool $luxCategory = false;
    protected bool $luxCategoryCompany = false;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function isLuxCategory(): bool
    {
        return $this->luxCategory;
    }

    public function setLuxCategory(bool $luxCategory): self
    {
        $this->luxCategory = $luxCategory;
        return $this;
    }

    public function isLuxCategoryCompany(): bool
    {
        return $this->luxCategoryCompany;
    }

    public function setLuxCategoryCompany(bool $luxCategoryCompany): self
    {
        $this->luxCategoryCompany = $luxCategoryCompany;
        return $this;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Metadata extends AbstractEntity
{
    const TABLE_NAME = 'sys_file_metadata';

    /**
     * @var ?ObjectStorage<Category>
     */
    protected ?ObjectStorage $categories = null;

    public function __construct()
    {
        $this->categories = new ObjectStorage();
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

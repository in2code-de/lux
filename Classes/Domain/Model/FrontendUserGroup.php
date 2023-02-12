<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUserGroup extends AbstractEntity
{
    protected string $title = '';
    protected string $description = '';

    /**
     * @var ObjectStorage<FrontendUserGroup>
     */
    protected ObjectStorage $subgroup;

    public function __construct(string $title = '')
    {
        $this->setTitle($title);
        $this->subgroup = new ObjectStorage();
    }

    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setSubgroup(ObjectStorage $subgroup): self
    {
        $this->subgroup = $subgroup;
        return $this;
    }

    public function addSubgroup(FrontendUserGroup $subgroup): self
    {
        $this->subgroup->attach($subgroup);
        return $this;
    }

    public function removeSubgroup(FrontendUserGroup $subgroup): self
    {
        $this->subgroup->detach($subgroup);
        return $this;
    }

    public function getSubgroup(): ObjectStorage
    {
        return $this->subgroup;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class File extends AbstractEntity
{
    const TABLE_NAME = 'sys_file';

    protected string $name = '';

    protected ?Metadata $metadata = null;

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function setMetadata(Metadata $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}

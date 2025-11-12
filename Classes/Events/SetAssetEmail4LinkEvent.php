<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Core\Resource\File;

final class SetAssetEmail4LinkEvent
{
    public function __construct(
        private readonly Visitor $visitor,
        private string $href,
        private File $file
    ) {
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function setHref(string $href): self
    {
        $this->href = $href;
        return $this;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;
        return $this;
    }
}

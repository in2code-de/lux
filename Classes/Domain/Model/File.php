<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class File
 */
class File extends AbstractEntity
{
    const TABLE_NAME = 'sys_file';

    /**
     * @var \In2code\Lux\Domain\Model\Metadata
     */
    protected $metadata = null;

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    /**
     * @param Metadata $metadata
     * @return File
     */
    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }
}

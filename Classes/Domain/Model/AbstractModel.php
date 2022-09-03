<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AbstractModel
 */
abstract class AbstractModel extends AbstractEntity
{
    /**
     * All records should be stored with sys_language_uid=-1 to get those values from persisted objects
     * in fe requests in every language
     *
     * @var int
     */
    protected $_languageUid = -1;
}

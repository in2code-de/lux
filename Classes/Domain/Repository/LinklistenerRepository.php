<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LinklistenerRepository
 */
class LinklistenerRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];
}

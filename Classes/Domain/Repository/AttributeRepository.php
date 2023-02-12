<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;

class AttributeRepository extends AbstractRepository
{
    public function findByVisitorAndKey(Visitor $visitor, string $key): ?Attribute
    {
        $query = $this->createQuery();
        $logicalAnd = [
            $query->equals('visitor', $visitor),
            $query->equals('name', $key),
        ];
        $query->matching($query->logicalAnd(...$logicalAnd));
        /** @var Attribute $attribute */
        $attribute = $query->execute()->getFirst();
        return $attribute;
    }
}

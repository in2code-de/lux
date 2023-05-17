<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Company;

class CompanyRepository extends AbstractRepository
{
    public function findByTitleAndDomain(string $title, string $domain): ?Company
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('title', $title),
                $query->equals('domain', $domain)
            )
        );
        return $query->execute()->getFirst();
    }
}

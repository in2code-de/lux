<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Model\Transfer;

final readonly class CountedVisitReference
{
    public function __construct(
        public string $table,
        public int $uid,
        public int $crdate
    ) {
    }
}

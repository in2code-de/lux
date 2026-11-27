<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Transfer\CountedVisitReference;
use In2code\Lux\Domain\Repository\CountedVisitRepository;
use TYPO3\CMS\Core\Crypto\HashService;

class CountedVisitReferenceService
{
    public const LIFETIME = 1800;
    protected const CLOCK_SKEW = 60;
    protected const SECRET_PREFIX = 'lux_counted_visit_';
    protected const SEPARATOR = '.';
    protected const REFERENCE_PATTERN = '~^([1-9][0-9]{0,9})\.([1-9][0-9]{0,10})\.([a-f0-9]{40})$~';

    public function __construct(protected readonly HashService $hashService)
    {
    }

    public function createReference(CountedVisitReference $reference): string
    {
        $payload = $reference->uid . self::SEPARATOR . $reference->crdate;
        return $payload . self::SEPARATOR . $this->hashService->hmac($payload, $this->getSecret($reference->table));
    }

    public function resolveReference(string $table, string $reference, int $now): ?CountedVisitReference
    {
        $resolved = null;
        if (
            in_array($table, CountedVisitRepository::TABLES, true)
            && preg_match(self::REFERENCE_PATTERN, $reference, $parts) === 1
            && $this->hashService->validateHmac(
                $parts[1] . self::SEPARATOR . $parts[2],
                $this->getSecret($table),
                $parts[3]
            )
            && $this->isWithinLifetime((int)$parts[2], $now)
        ) {
            $resolved = new CountedVisitReference($table, (int)$parts[1], (int)$parts[2]);
        }
        return $resolved;
    }

    protected function isWithinLifetime(int $crdate, int $now): bool
    {
        return $crdate >= $now - self::LIFETIME && $crdate <= $now + self::CLOCK_SKEW;
    }

    protected function getSecret(string $table): string
    {
        return self::SECRET_PREFIX . $table;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service\Uri;

/**
 * Interface RecordInterface
 */
interface RecordInterface
{
    public function get(string $tableName, int $pageIdentifier, bool $addReturnUrl = true): string;
}

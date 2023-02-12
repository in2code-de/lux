<?php

declare(strict_types=1);
namespace In2code\Lux\TCA;

use In2code\Lux\Domain\Model\Log;

class GetStatusForLogSelection
{
    public function addOptions(array &$params): void
    {
        $params['items'] = $this->getStatusItemsFromLogModel();
    }

    protected function getStatusItemsFromLogModel(): array
    {
        $log = new \ReflectionClass(Log::class);
        $constants = $log->getConstants();
        $items = [];
        foreach (array_keys($constants) as $key) {
            if (stristr($key, 'STATUS_')) {
                $items[] = [$key, $constants[$key]];
            }
        }
        return $items;
    }
}

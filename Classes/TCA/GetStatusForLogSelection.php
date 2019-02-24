<?php
declare(strict_types=1);
namespace In2code\Lux\TCA;

use In2code\Lux\Domain\Model\Log;

/**
 * Class GetStatusForLogSelection
 */
class GetStatusForLogSelection
{

    /**
     * @param array $params
     * @return void
     * @throws \ReflectionException
     */
    public function addOptions(array &$params)
    {
        $params['items'] = $this->getStatusItemsFromLogModel();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
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

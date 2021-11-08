<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

/**
 * LayerInterface
 */
interface LayerInterface
{
    /**
     * @return array
     */
    public function getCachableArguments(): array;

    /**
     * @return array
     */
    public function getUncachableArguments(): array;
}

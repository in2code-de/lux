<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

/**
 * Interface DataProviderInterface
 */
interface DataProviderInterface
{
    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @return void
     */
    public function prepareData(): void;
}

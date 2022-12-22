<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

interface DataProviderInterface
{
    public function getData(): array;
    public function prepareData(): void;
}

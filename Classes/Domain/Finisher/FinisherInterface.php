<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

interface FinisherInterface
{
    public function handle(): void;
    public function start(): array;
    public function shouldFinisherRun(): bool;
}

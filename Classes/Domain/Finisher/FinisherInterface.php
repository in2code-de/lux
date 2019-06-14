<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

/**
 * Interface FinisherInterface
 */
interface FinisherInterface
{
    /**
     * @return void
     */
    public function handle();

    /**
     * @return array
     */
    public function start(): array;

    /**
     * @return bool
     */
    public function shouldFinisherRun(): bool;
}

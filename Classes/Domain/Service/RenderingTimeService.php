<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use In2code\Lux\Exception\UnexpectedValueException;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * RenderingTimeService
 * is a simple class that holds the time when first initialized. Any time later the difference can be shown.
 */
class RenderingTimeService implements SingletonInterface
{
    protected bool $started = false;

    /**
     * Time in seconds (with microseconds as decimal place)
     *
     * @var float
     */
    protected float $timeStart = 0.0;

    public function __construct()
    {
        $this->start();
    }

    public function start(): self
    {
        if ($this->isRunning() === false) {
            $this->started = true;
            $this->timeStart = microtime(true);
        }
        return $this;
    }

    public function isRunning(): bool
    {
        return $this->started;
    }

    /**
     * @return string
     * @throws UnexpectedValueException
     */
    public function getTime(): string
    {
        if ($this->isRunning() === false) {
            throw new UnexpectedValueException(__CLASS__ . ' was not initialized correctly', 1636304996);
        }
        $result = microtime(true) - $this->timeStart;
        return number_format($result, 5);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

final class ReadableReferrersEvent
{
    protected string $referrer;
    protected array $sources;

    public function __construct(string $referrer, array $sources)
    {
        $this->referrer = $referrer;
        $this->sources = $sources;
    }

    public function getReferrer(): string
    {
        return $this->referrer;
    }

    public function setReferrer(string $referrer): self
    {
        $this->referrer = $referrer;
        return $this;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function setSources(array $sources): self
    {
        $this->sources = $sources;
        return $this;
    }
}

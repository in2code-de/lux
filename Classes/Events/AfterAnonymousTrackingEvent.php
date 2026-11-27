<?php

declare(strict_types=1);

namespace In2code\Lux\Events;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Dispatched instead of AfterTrackingEvent for requests that are needed without an opt-in (e.g.
 * "redirectRequestAction" to get the target of a shortener). There is no visitor, so listeners may only count
 * anonymously and must not store personal data.
 */
final class AfterAnonymousTrackingEvent
{
    /**
     * JSON result for AJAX request
     */
    protected array $results = [];

    public function __construct(
        protected readonly string $actionMethodName,
        protected readonly array $arguments,
        protected readonly ServerRequestInterface $request
    ) {
    }

    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $name): mixed
    {
        return $this->arguments[$name] ?? null;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function addResult(array $result): self
    {
        if ($result !== []) {
            $this->results = array_merge([$result], $this->results);
        }
        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}

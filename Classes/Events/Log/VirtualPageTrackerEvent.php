<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Visitor;

final class VirtualPageTrackerEvent
{
    protected Visitor $visitor;
    protected string $parameter;
    protected array $arguments;

    public function __construct(Visitor $visitor, string $parameter, array $arguments)
    {
        $this->visitor = $visitor;
        $this->parameter = $parameter;
        $this->arguments = $arguments;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}

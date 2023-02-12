<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Visitor;

final class LogEmail4linkSendEmailFailedEvent
{
    protected Visitor $visitor;

    protected string $href = '';

    public function __construct(Visitor $visitor, string $href)
    {
        $this->visitor = $visitor;
        $this->href = $href;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function setHref(string $href): self
    {
        $this->href = $href;
        return $this;
    }
}

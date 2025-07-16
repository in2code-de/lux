<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;

final class VisitorFactoryBeforeCreateNewEvent
{
    protected ?Visitor $visitor = null;
    protected ?Fingerprint $fingerprint = null;

    public function __construct(?Visitor $visitor = null, ?Fingerprint $fingerprint = null)
    {
        $this->visitor = $visitor;
        $this->fingerprint = $fingerprint;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getFingerprint(): Fingerprint
    {
        return $this->fingerprint;
    }
}

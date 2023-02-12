<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;

final class VisitorFactoryAfterCreateNewEvent
{
    protected Visitor $visitor;
    protected Fingerprint $fingerprint;

    public function __construct(Visitor $visitor, Fingerprint $fingerprint)
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

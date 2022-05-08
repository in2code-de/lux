<?php
declare(strict_types = 1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;

final class VisitorFactoryBeforeCreateNewEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Fingerprint
     */
    protected $fingerprint;

    /**
     * @param Visitor|null $visitor
     * @param Fingerprint|null $fingerprint
     */
    public function __construct(Visitor $visitor = null, Fingerprint $fingerprint = null)
    {
        $this->visitor = $visitor;
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Fingerprint
     */
    public function getFingerprint(): Fingerprint
    {
        return $this->fingerprint;
    }
}

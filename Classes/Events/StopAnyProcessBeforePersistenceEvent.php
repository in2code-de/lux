<?php
declare(strict_types = 1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Fingerprint;

final class StopAnyProcessBeforePersistenceEvent
{
    /**
     * @var Fingerprint
     */
    protected $fingerprint;

    /**
     * @param Fingerprint $fingerprint
     */
    public function __construct(Fingerprint $fingerprint)
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return Fingerprint
     */
    public function getFingerprint(): Fingerprint
    {
        return $this->fingerprint;
    }
}

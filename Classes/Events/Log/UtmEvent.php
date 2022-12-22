<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Utm;

final class UtmEvent
{
    protected Utm $utm;

    public function __construct(Utm $utm)
    {
        $this->utm = $utm;
    }

    public function getUtm(): Utm
    {
        return $this->utm;
    }
}

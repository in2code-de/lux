<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Referrer;

use In2code\Lux\Domain\Service\Referrer\Readable;

class ReadableFixture extends Readable
{
    public function __construct(string $referrer = '')
    {
        $this->referrer = $referrer;
    }
}

<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator;

class TrueActivator extends AbstractActivator
{
    public function isActive(): bool
    {
        return true;
    }
}

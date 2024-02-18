<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator;

interface ActivatorInterface
{
    public function isActive(): bool;
}

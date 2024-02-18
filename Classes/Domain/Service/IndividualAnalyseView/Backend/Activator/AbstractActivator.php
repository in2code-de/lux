<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator;

abstract class AbstractActivator implements ActivatorInterface
{
    protected array $configuration = [];

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }
}

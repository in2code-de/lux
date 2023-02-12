<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

class DisableEmail4LinkFinisher extends AbstractFinisher implements FinisherInterface
{
    public function shouldFinisherRun(): bool
    {
        return $this->getVisitor()->isIdentified() && $this->getConfigurationByKey('enable') === '1';
    }

    public function start(): array
    {
        return [
            'action' => 'disableEmail4Link',
            'configuration' => [],
            'finisher' => 'DisableEmail4Link',
        ];
    }
}

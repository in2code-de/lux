<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Finisher;

/**
 * Class DisableEmail4LinkFinisher
 */
class DisableEmail4LinkFinisher extends AbstractFinisher implements FinisherInterface
{
    /**
     * @return bool
     */
    public function shouldFinisherRun(): bool
    {
        return $this->getVisitor()->isIdentified() && $this->getConfigurationByKey('enable') === '1';
    }

    /**
     * @return array
     */
    public function start(): array
    {
        return [
            'action' => 'disableEmail4Link',
            'configuration' => [],
            'finisher' => 'DisableEmail4Link'
        ];
    }
}

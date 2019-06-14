<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\ObjectUtility;

/**
 * Class FinisherHandler
 */
class FinisherHandler
{
    /**
     * @param Visitor $visitor
     * @param string $controllerAction
     * @param array $actions
     * @return array
     */
    public function startFinisher(Visitor $visitor, string $controllerAction, array $actions): array
    {
        foreach ($this->getFinisherClassConfiguration() as $fConfiguration) {
            /** @var AbstractFinisher $instance */
            $instance = ObjectUtility::getObjectManager()->get(
                $fConfiguration['class'],
                $visitor,
                $controllerAction,
                $actions,
                $fConfiguration['configuration']
            );
            $actions = $instance->handle();
        }
        return [$visitor, $controllerAction, $actions];
    }

    /**
     * @return array
     */
    protected function getFinisherClassConfiguration(): array
    {
        $configuration = [];
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        foreach ((array)$settings['finisher'] as $fConfiguration) {
            if (!class_exists($fConfiguration['class'])) {
                throw new \UnexpectedValueException(
                    'Finisher class ' . $fConfiguration['class'] . ' does not exists, can not be loaded',
                    1560510775
                );
            }
            if (is_subclass_of($fConfiguration['class'], FinisherInterface::class) === false) {
                throw new \UnexpectedValueException(
                    'Finisher class ' . $fConfiguration['class'] . ' does not implement ' . FinisherInterface::class,
                    1560510886
                );
            }
            $configuration[] = $fConfiguration;
        }
        return $configuration;
    }
}

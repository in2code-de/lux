<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Finisher;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Exception\ClassDoesNotExistException;
use In2code\Lux\Exception\InterfaceIsMissingException;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class FinisherHandler
 */
class FinisherHandler
{
    /**
     * @param Visitor $visitor
     * @param string $controllerAction
     * @param array $actions
     * @param array $parameters
     * @return array
     * @throws ClassDoesNotExistException
     * @throws Exception
     * @throws InterfaceIsMissingException
     */
    public function startFinisher(
        Visitor $visitor,
        string $controllerAction,
        array $actions,
        array $parameters = []
    ): array {
        foreach ($this->getFinisherClassConfiguration() as $fConfiguration) {
            /** @var AbstractFinisher $instance */
            $instance = ObjectUtility::getObjectManager()->get(
                $fConfiguration['class'],
                $visitor,
                $controllerAction,
                $actions,
                $parameters,
                $fConfiguration['configuration']
            );
            $actions = $instance->handle();
        }
        return [$visitor, $controllerAction, $actions, $parameters];
    }

    /**
     * @return array
     * @throws ClassDoesNotExistException
     * @throws InterfaceIsMissingException
     * @throws Exception
     */
    protected function getFinisherClassConfiguration(): array
    {
        $configuration = [];
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        foreach ((array)$settings['finisher'] as $fConfiguration) {
            if (!class_exists($fConfiguration['class'])) {
                throw new ClassDoesNotExistException(
                    'Finisher class ' . $fConfiguration['class'] . ' does not exists, can not be loaded',
                    1560510775
                );
            }
            if (is_subclass_of($fConfiguration['class'], FinisherInterface::class) === false) {
                throw new InterfaceIsMissingException(
                    'Finisher class ' . $fConfiguration['class'] . ' does not implement ' . FinisherInterface::class,
                    1560510886
                );
            }
            $configuration[] = $fConfiguration;
        }
        return $configuration;
    }
}

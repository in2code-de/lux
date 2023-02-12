<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

use In2code\Lux\Events\AfterTrackingEvent;
use In2code\Lux\Exception\ClassDoesNotExistException;
use In2code\Lux\Exception\InterfaceIsMissingException;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class FinisherHandler
{
    /**
     * @param AfterTrackingEvent $event
     * @return void
     * @throws ClassDoesNotExistException
     * @throws InterfaceIsMissingException
     * @throws InvalidConfigurationTypeException
     */
    public function __invoke(AfterTrackingEvent $event): void
    {
        foreach ($this->getFinisherClassConfiguration() as $fConfiguration) {
            /** @var AbstractFinisher $instance */
            $instance = GeneralUtility::makeInstance(
                $fConfiguration['class'],
                $event,
                $fConfiguration['configuration'] ?? []
            );
            $instance->handle();
        }
    }

    /**
     * @return array
     * @throws ClassDoesNotExistException
     * @throws InterfaceIsMissingException
     * @throws InvalidConfigurationTypeException
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

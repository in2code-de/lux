<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\IndividualAnalyseView;

use In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator\AbstractActivator;
use In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator\ActivatorInterface;
use In2code\Lux\Exception\ConfigurationException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Helper
{
    /**
     * @return array
     * @throws ConfigurationException
     */
    public function getActivatedViews(): array
    {
        $activatedViews = [];
        foreach ($this->getAllViews() as $view) {
            $configuration = $view['backend']['activated'] ?? [];
            if ($this->isViewActivated($configuration)) {
                $activatedViews[] = $view;
            }
        }
        return $activatedViews;
    }

    protected function getAllViews(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['lux']['individualAnalyseViews'] ?? [];
    }

    /**
     * @param array $configuration
     * @return bool
     * @throws ConfigurationException
     */
    protected function isViewActivated(array $configuration): bool
    {
        if (class_exists($configuration['class'] ?? '') === false) {
            throw new ConfigurationException('Class ' . $configuration['class'] . ' does not exist', 1708268767);
        }
        if (is_subclass_of($configuration['class'], ActivatorInterface::class) === false) {
            throw new ConfigurationException(
                'Class ' . $configuration['class'] . ' does not implement ' . ActivatorInterface::class,
                1708268868
            );
        }
        /** @var AbstractActivator $activator */
        $activator = GeneralUtility::makeInstance($configuration['class'])
            ->setConfiguration($configuration['configuration'] ?? []);
        return $activator->isActive();
    }
}

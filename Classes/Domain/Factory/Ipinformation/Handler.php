<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\IpinformationServiceConnectionFailureException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class Handler
{
    protected ?ConfigurationService $configurationService = null;
    protected ?IpinformationRepository $ipinformationRepository = null;

    public function __construct(
        ConfigurationService $configurationService,
        IpinformationRepository $ipinformationRepository
    ) {
        $this->configurationService = $configurationService;
        $this->ipinformationRepository = $ipinformationRepository;
    }

    /**
     * @return ObjectStorage
     * @throws ConfigurationException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     */
    public function getObjectStorage(): ObjectStorage
    {
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        /** @var IpinformationInterface $object */
        foreach ($this->getServiceObjects() as $object) {
            try {
                $result = $object->start();
                if ($result !== []) {
                    foreach ($result as $key => $value) {
                        if (!empty($value)) {
                            $ipinformation = GeneralUtility::makeInstance(Ipinformation::class);
                            $ipinformation->setName($key)->setValue((string)$value);
                            $this->ipinformationRepository->add($ipinformation);
                            $objectStorage->attach($ipinformation);
                        }
                    }
                    break;
                }
            } catch (IpinformationServiceConnectionFailureException $exception) {
                continue;
            }
        }
        return $objectStorage;
    }

    /**
     * @return array
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getServiceObjects(): array
    {
        $classes = [];
        $settings = $this->configurationService->getTypoScriptSettings();
        if (isset($settings['ipinformation']['_enable']) && $settings['ipinformation']['_enable'] === '1') {
            foreach (array_keys((array)$settings['ipinformation']) as $key) {
                if (MathUtility::canBeInterpretedAsInteger($key)) {
                    $classes[] = $this->getServiceClassByIdentifier((int)$key);
                }
            }
        }
        return $classes;
    }

    /**
     * @param int $identifier
     * @return IpinformationInterface
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    public function getServiceClassByIdentifier(int $identifier): IpinformationInterface
    {
        $settings = $this->configurationService->getTypoScriptSettings();
        if (!empty($settings['ipinformation'][(string)$identifier]['class'])) {
            /** @var IpinformationInterface $service */
            $service = GeneralUtility::makeInstance(
                $settings['ipinformation'][(string)$identifier]['class'],
                (array)$settings['ipinformation'][(string)$identifier]['configuration']
            );
            return $service;
        }
        throw new ConfigurationException('Service class not defined for ipinformation', 1631736619);
    }
}

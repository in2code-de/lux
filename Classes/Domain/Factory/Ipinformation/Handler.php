<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\IpinformationServiceConnectionFailureException;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Handler
 */
final class Handler
{
    /**
     * @var ConfigurationService|null
     */
    protected $configurationService = null;

    /**
     * @var IpinformationRepository|null
     */
    protected $ipinformationRepository = null;

    /**
     * Constructor
     *
     * @param ConfigurationService|null $configurationService
     * @param IpinformationRepository|null $ipinformationRepository
     * @throws Exception
     */
    public function __construct(
        ConfigurationService $configurationService = null,
        IpinformationRepository $ipinformationRepository = null
    ) {
        $this->configurationService
            = $configurationService ?: GeneralUtility::makeInstance(ConfigurationService::class);
        $this->ipinformationRepository
            = $ipinformationRepository ?: ObjectUtility::getObjectManager()->get(IpinformationRepository::class);
    }

    /**
     * @return ObjectStorage
     * @throws ConfigurationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     */
    public function getObjectStorage(): ObjectStorage
    {
        $objectStorage = ObjectUtility::getObjectManager()->get(ObjectStorage::class);
        /** @var IpinformationInterface $object */
        foreach ($this->getServiceObjects() as $object) {
            try {
                $result = $object->start();
                if ($result !== []) {
                    foreach ($result as $key => $value) {
                        if (!empty($value)) {
                            $ipinformation = ObjectUtility::getObjectManager()->get(Ipinformation::class);
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

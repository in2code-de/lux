<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use In2code\Lux\Exception\ConnectionFailedException;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class IpinformationFactory get a new ObjectStorage with relevant Ip-Information
 */
class IpinformationFactory
{
    /**
     * @var array
     */
    protected $storeByKey = [
        'country',
        'countryCode',
        'region',
        'city',
        'zip',
        'lat',
        'lon',
        'isp',
        'org'
    ];

    /**
     * @return ObjectStorage
     * @throws ConnectionFailedException
     * @throws Exception
     */
    public function getObjectStorageWithIpinformation(): ObjectStorage
    {
        $objectStorage = ObjectUtility::getObjectManager()->get(ObjectStorage::class);
        $ipinformationRepo = ObjectUtility::getObjectManager()->get(IpinformationRepository::class);
        $information = $this->getInformationFromIp();
        foreach ($information as $key => $value) {
            if (in_array($key, $this->storeByKey)) {
                $ipinformation = ObjectUtility::getObjectManager()->get(Ipinformation::class);
                $ipinformation->setName($key)->setValue((string)$value);
                $ipinformationRepo->add($ipinformation);
                $objectStorage->attach($ipinformation);
            }
        }
        /** @var ObjectStorage $objectStorage */
        return $objectStorage;
    }

    /**
     * Get information out of an IP address
     *
     * @return array
     * @throws ConnectionFailedException
     */
    protected function getInformationFromIp(): array
    {
        $ipAddress = IpUtility::getIpAddress();
        $properties = [];
        $json = GeneralUtility::getUrl('http://ip-api.com/json/' . $ipAddress);
        if ($json === false) {
            throw new ConnectionFailedException('Could not connect to http://ip-api.com', 1518208369);
        }
        if (!empty($json)) {
            $properties = json_decode($json, true);
        }
        return $properties;
    }
}

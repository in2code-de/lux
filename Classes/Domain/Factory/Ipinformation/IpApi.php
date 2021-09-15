<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

/**
 * IpApi
 */
class IpApi extends AbstractIpinformation implements IpinformationInterface
{
    /**
     * @var array
     */
    protected $mapping = [
        'org' => 'org',
        'country' => 'country',
        'countryCode' => 'countryCode',
        'city' => 'city',
        'lat' => 'lat',
        'lon' => 'lon',
        'zip' => 'zip',
        'region' => 'region',
    ];
}

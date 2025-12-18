<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

class IpApiIs extends AbstractIpinformation implements IpinformationInterface
{
    /**
     * Key in LUX => Key from request
     *
     * @var array|string[]
     */
    protected array $mapping = [
        'company.name' => 'org',
        'company.domain' => 'domain',
        'location.country' => 'country',
        'location.country_code' => 'countryCode',
        'location.city' => 'city',
        'location.latitude' => 'lat',
        'location.longitude' => 'lon',
        'location.zip' => 'zip',
        'location.state' => 'region',
    ];
}

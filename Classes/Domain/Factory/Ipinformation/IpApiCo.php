<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

class IpApiCo extends AbstractIpinformation implements IpinformationInterface
{
    protected array $mapping = [
        'org' => 'org',
        'country_name' => 'country',
        'country_code' => 'countryCode',
        'city' => 'city',
        'latitude' => 'lat',
        'longitude' => 'lon',
        'region' => 'region',
    ];
}

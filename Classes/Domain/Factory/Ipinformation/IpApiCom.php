<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

class IpApiCom extends AbstractIpinformation implements IpinformationInterface
{
    protected array $mapping = [
        'country_name' => 'country',
        'country_code' => 'countryCode',
        'city' => 'city',
        'location.latitude' => 'lat',
        'location.longitude' => 'lon',
        'zip' => 'zip',
        'region_name' => 'region',
    ];
}

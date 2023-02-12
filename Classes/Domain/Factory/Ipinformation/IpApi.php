<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

class IpApi extends AbstractIpinformation implements IpinformationInterface
{
    protected array $mapping = [
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

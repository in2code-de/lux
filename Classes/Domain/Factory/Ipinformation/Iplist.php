<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

class Iplist extends AbstractIpinformation implements IpinformationInterface
{
    protected array $mapping = [
        'countryname' => 'country', // name of country - like "Germany"
        'countrycode' => 'countryCode', // ISO code of country - like "DE"
        'detail' => 'org', // name of the organisation - like "Company A"
    ];
}

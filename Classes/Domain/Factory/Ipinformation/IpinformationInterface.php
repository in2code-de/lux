<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

/**
 * IpinformationInterface
 */
interface IpinformationInterface
{
    /**
     * Set the url of the service. Normally the url contains the visitor IP address.
     *
     * @param string $url
     * @return void
     */
    public function setUrl(string $url): void;

    /**
     * Convert the content result to an array (e.g. json to array or xml to array).
     *
     * @param string $content
     * @return array
     */
    public function convertResultToArray(string $content): array;

    /**
     * Map keys to allowed keys. Look at AbstractIpinformation for a list of official keys.
     *
     * @param array $array
     * @return array
     */
    public function mapKeys(array $array): array;
}

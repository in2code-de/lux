<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory\Ipinformation;

use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\IpinformationServiceConnectionFailureException;
use In2code\Lux\Utility\IpUtility;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractIpinformation
{
    /**
     * Map key of interface with key in database
     *
     * @var array
     */
    protected array $mapping = [
        'org' => 'org', // name of the organisation - like "Company A"
        'country' => 'country', // name of country - like "Germany"
        'countryCode' => 'countryCode', // ISO code of country - like "DE"
        'city' => 'city', // name of the city - like "Berlin"
        'lat' => 'lat', // latitude - like 52.4922
        'lon' => 'lon', // longitude - like 13.5265
        'zip' => 'zip', // zip code - like 12345
        'region' => 'region', // region code - like "BE" for Berlin
    ];

    /**
     * Contains url to connect to
     *
     * @var string
     */
    private string $url = '';

    /**
     * Contains configuration from TypoScript (e.g. URL)
     *
     * @var array
     */
    protected array $configuration = [];

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     * @throws ConfigurationException
     * @throws IpinformationServiceConnectionFailureException
     */
    final public function start(): array
    {
        if (empty($this->configuration['url'])) {
            throw new ConfigurationException('URL is not configured', 1631737978);
        }
        $this->setUrl($this->configuration['url']);
        return $this->connect();
    }

    /**
     * Connect to service. If IpinformationServiceConnectionFailureException is thrown, next service will be called.
     *
     * @return array
     * @throws IpinformationServiceConnectionFailureException
     */
    private function connect(): array
    {
        try {
            $response = GeneralUtility::makeInstance(RequestFactory::class)->request($this->url);
            if ($response->getStatusCode() !== 200) {
                throw new IpinformationServiceConnectionFailureException($response->getReasonPhrase(), 1631737255);
            }
            $content = $response->getBody()->getContents();
        } catch (Throwable $exception) {
            throw new IpinformationServiceConnectionFailureException($exception->getMessage(), 1631742005);
        }
        $array = $this->convertResultToArray($content);
        $array = $this->mapKeys($array);
        return $array;
    }

    public function convertResultToArray(string $content): array
    {
        return (array)json_decode($content, true);
    }

    public function mapKeys(array $array): array
    {
        $newArray = [];
        foreach ($this->mapping as $from => $to) {
            if (array_key_exists($from, $array)) {
                $newArray[$to] = $array[$from];
            }
        }
        return $newArray;
    }

    public function setUrl(string $url): void
    {
        $url = str_replace('{ip}', IpUtility::getIpAddress(), $url);
        $this->url = $url;
    }
}

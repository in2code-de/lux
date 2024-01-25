<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Exception\ParametersException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UtmFactory
{
    protected array $utmKeys = [
        'utm_source' => [
            'utm_source',
            'mtm_source',
        ],
        'utm_medium' => [
            'utm_medium',
            'mtm_medium',
        ],
        'utm_campaign' => [
            'utm_campaign',
            'mtm_campaign',
        ],
        'utm_id' => [
            'utm_id',
            'mtm_cid',
        ],
        'utm_term' => [
            'utm_term',
            'mtm_kwd',
        ],
        'utm_content' => [
            'utm_content',
            'mtm_content',
        ],
    ];

    /**
     * @throws ParametersException
     */
    public function get(array $parameters, string $referrer): Utm
    {
        $utm = GeneralUtility::makeInstance(Utm::class);
        $utm->setReferrer($referrer);
        foreach ($this->getUtmKeysForDatabase() as $key) {
            if (array_key_exists($key, $parameters) === false) {
                throw new ParametersException($key . ' is not existing in given array', 1666207599);
            }
            if ($parameters[$key] !== null) {
                $utm->{'set' . GeneralUtility::underscoredToUpperCamelCase($key)}(trim($parameters[$key]));
            }
        }
        return $utm;
    }

    public function getUtmKeys(): array
    {
        return $this->utmKeys;
    }

    public function getUtmKeysForDatabase(): array
    {
        return array_keys($this->utmKeys);
    }

    /**
     * Example return:
     *  [
     *      "utm_source",
     *      "mtm_source",
     *      "utm_campaign",
     *      ...
     *  ]
     *
     * @return array
     */
    public function getAllUtmKeys(): array
    {
        $keys = [];
        foreach ($this->utmKeys as $keysSub) {
            foreach ($keysSub as $key) {
                $keys[] = $key;
            }
        }
        return $keys;
    }
}

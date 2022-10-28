<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Exception\ParametersException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UtmFactory
{
    protected $utmKeys = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_id',
        'utm_term',
        'utm_content',
    ];

    /**
     * @throws ParametersException
     */
    public function get(array $parameters): Utm
    {
        $utm = GeneralUtility::makeInstance(Utm::class);
        foreach ($this->getUtmKeys() as $key) {
            if (array_key_exists($key, $parameters) === false) {
                throw new ParametersException($key . ' is not existing in given array', 1666207599);
            }
            if ($parameters[$key] !== null) {
                $utm->{'set' . GeneralUtility::underscoredToUpperCamelCase($key)}($parameters[$key]);
            }
        }
        return $utm;
    }

    public function getUtmKeys(): array
    {
        return $this->utmKeys;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\TCA;

use In2code\Lux\Utility\LocalizationUtility;

/**
 * Class VisitorTitle
 */
class VisitorTitle
{
    /**
     * @param array $parameters
     * @param object $parentObject
     * @return void
     */
    public function getContactTitle(array &$parameters, $parentObject)
    {
        unset($parentObject);
        $parameters['title'] = $this->getEmail($parameters['row']) . ' (uid' . $parameters['row']['uid'] . ')';
    }

    /**
     * @param array $properties
     * @return string
     */
    protected function getEmail(array $properties): string
    {
        $email = LocalizationUtility::translateByKey('anonym');
        if (!empty($properties['email'])) {
            $email = $properties['email'];
        }
        return $email;
    }
}

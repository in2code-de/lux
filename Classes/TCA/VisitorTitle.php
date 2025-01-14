<?php

declare(strict_types=1);
namespace In2code\Lux\TCA;

use In2code\Lux\Utility\LocalizationUtility;

class VisitorTitle
{
    public function getContactTitle(array &$parameters, $parentObject): void
    {
        unset($parentObject);
        $parameters['title'] = $this->getEmail($parameters['row'] ?? []) . ' (uid' . $parameters['row']['uid'] . ')';
    }

    protected function getEmail(array $properties): string
    {
        $email = LocalizationUtility::translateByKey('anonym');
        if (!empty($properties['email'])) {
            $email = $properties['email'];
        }
        return $email;
    }
}

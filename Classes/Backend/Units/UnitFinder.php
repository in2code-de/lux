<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Units;

use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UnitFinder
{
    /**
     * @param string $path
     * @return UnitInterface
     * @throws ConfigurationException
     */
    public function find(string $path): UnitInterface
    {
        $pathParts = StringUtility::splitCamelcaseString($path);
        $classNameUnit = 'In2code\Lux\Backend\Units\\' . implode('\\', $pathParts);
        if (class_exists($classNameUnit) === false) {
            throw new ConfigurationException('Given class ' . $classNameUnit . ' does not exists', 1694522397);
        }
        /** @noinspection PhpParamsInspection */
        return GeneralUtility::makeInstance($classNameUnit);
    }
}

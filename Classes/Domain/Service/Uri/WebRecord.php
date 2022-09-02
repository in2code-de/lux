<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Uri;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;

/**
 * Class EditRecord
 */
class WebRecord extends AbstractRecord
{
    /**
     * @param string $tableName
     * @param int $identifier
     * @param bool $addReturnUrl
     * @return string
     * @throws RouteNotFoundException
     */
    public function get(string $tableName, int $identifier, bool $addReturnUrl = true): string
    {
        $uriParameters = [
            'id' => $identifier,
        ];
        return $this->getRoute('web_layout', $uriParameters);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Uri;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;

/**
 * Class NewRecord
 */
class NewRecord extends AbstractRecord
{
    /**
     * @param string $tableName
     * @param int $pageIdentifier where to save the new record
     * @param bool $addReturnUrl
     * @return string
     * @throws RouteNotFoundException
     */
    public function get(string $tableName, int $pageIdentifier, bool $addReturnUrl = true): string
    {
        $uriParameters = [
            'edit' => [
                $tableName => [
                    $pageIdentifier => 'new',
                ],
            ],
        ];
        if ($addReturnUrl) {
            $uriParameters['returnUrl'] = $this->getReturnUrl();
        }
        return $this->getRoute('record_edit', $uriParameters);
    }
}

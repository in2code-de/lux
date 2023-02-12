<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Uri;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;

class EditRecord extends AbstractRecord
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
            'edit' => [
                $tableName => [
                    $identifier => 'edit',
                ],
            ],
        ];
        if ($addReturnUrl) {
            $uriParameters['returnUrl'] = $this->getReturnUrl();
        }
        return $this->getRoute('record_edit', $uriParameters);
    }
}

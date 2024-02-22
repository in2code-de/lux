<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUtility
{
    /**
     * @param string $property
     * @return string|int
     */
    public static function getPropertyFromBackendUser(string $property = 'uid')
    {
        if (!empty(self::getBackendUserAuthentication()->user[$property])) {
            return self::getBackendUserAuthentication()->user[$property];
        }
        return '';
    }

    public static function isBackendAuthentication(): bool
    {
        return self::getBackendUserAuthentication() !== null;
    }

    public static function isAdministrator(): bool
    {
        if (self::getBackendUserAuthentication() !== null) {
            return self::getBackendUserAuthentication()->isAdmin();
        }
        return false;
    }

    /**
     * @param string $key
     * @param string $action
     * @param string $controller
     * @param array $data
     * @return void
     * @codeCoverageIgnore
     */
    public static function saveValueToSession(string $key, string $action, string $controller, array $data)
    {
        self::getBackendUserAuthentication()->setAndSaveSessionData($key . $action . $controller . '_lux', $data);
    }

    public static function getSessionValue(string $key, string $action, string $controller): array
    {
        $value = self::getBackendUserAuthentication()->getSessionData($key . $action . $controller . '_lux');
        if (is_array($value) === true) {
            return $value;
        }
        return [];
    }

    /**
     * @return ?BackendUserAuthentication
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Class BackendUtility
 */
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

    /**
     * @return bool
     */
    public static function isAdministrator(): bool
    {
        if (self::getBackendUserAuthentication() !== null) {
            return self::getBackendUserAuthentication()->user['admin'] === 1;
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

    /**
     * @param string $key
     * @param string $action
     * @param string $controller
     * @return array
     */
    public static function getSessionValue(string $key, string $action, string $controller): array
    {
        return (array)self::getBackendUserAuthentication()->getSessionData($key . $action . $controller . '_lux');
    }

    /**
     * @return ?BackendUserAuthentication
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Class BackendUtility
 */
class BackendUtility
{

    /**
     * Get property from backend user
     *
     * @param string $property
     * @return string|int
     */
    public static function getPropertyFromBackendUser($property = 'uid')
    {
        if (!empty(self::getBackendUserAuthentication()->user[$property])) {
            return self::getBackendUserAuthentication()->user[$property];
        }
        return '';
    }

    /**
     * @param string $key
     * @param string $action
     * @param string $controller
     * @param array $data
     * @return void
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
     * @return BackendUserAuthentication
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}

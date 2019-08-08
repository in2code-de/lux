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
     * @param array $data
     * @return void
     */
    public static function saveValueToSession(string $key, array $data)
    {
        self::getBackendUserAuthentication()->setAndSaveSessionData($key . '_lux', $data);
    }

    /**
     * @param string $key
     * @return array
     */
    public static function getSessionValue(string $key): array
    {
        return (array)self::getBackendUserAuthentication()->getSessionData($key . '_lux');
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

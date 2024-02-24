<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use Throwable;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

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

    public static function saveValueToSession(string $key, string $action, string $controller, array $data): void
    {
        self::getBackendUserAuthentication()->setAndSaveSessionData($key . $action . $controller . '_lux', $data);
    }

    public static function getFilterFromSession(
        string $actionName,
        string $controllerName,
        array $propertyOverlay = []
    ): ?FilterDto {
        $filterArray = self::getFilterArrayFromSession($actionName, $controllerName, $propertyOverlay);
        try {
            return GeneralUtility::makeInstance(PropertyMapper::class)->convert($filterArray, FilterDto::class);
        } catch (Throwable $exception) {
            return null;
        }
    }

    public static function getFilterArrayFromSession(
        string $actionName,
        string $controllerName,
        array $propertyOverlay = []
    ): array {
        $filter = BackendUtility::getSessionValue('filter', $actionName, $controllerName);
        return array_merge($filter, $propertyOverlay);
    }

    public static function getSessionValue(string $key, string $action, string $controller): array
    {
        $value = self::getBackendUserAuthentication()->getSessionData($key . $action . $controller . '_lux');
        if (is_array($value) === true) {
            return $value;
        }
        return [];
    }

    public static function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}

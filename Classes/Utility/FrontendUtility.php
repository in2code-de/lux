<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use In2code\Lux\Domain\Service\SiteService;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class FrontendUtility
{
    public static function getCurrentPageIdentifier(): int
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $pageArguments = $request?->getAttribute('routing');
        if ($pageArguments instanceof PageArguments) {
            return $pageArguments->getPageId();
        }
        return 0;
    }

    /**
     * @return string "currentdomain.org"
     */
    public static function getCurrentDomain(): string
    {
        $currentDomain = GeneralUtility::getIndpEnv('HTTP_HOST');
        if ($currentDomain === null) {
            // Normally in CLI context
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $domain = $siteService->getFirstDomain();
            $currentDomain = preg_replace('~http(s)://|/~', '', $domain);
        }
        return $currentDomain;
    }

    /**
     * @return string "https://currentdomain.org"
     */
    public static function getCurrentHostAndDomain(): string
    {
        return GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
    }

    public static function isLoggedInFrontendUser(): bool
    {
        $authentication = self::getFrontendUserAuthentication();
        if ($authentication !== null) {
            return ($authentication->user['uid'] ?? 0) > 0;
        }
        return false;
    }

    public static function getPropertyFromLoggedInFrontendUser($propertyName = 'uid'): string
    {
        $authentication = self::getFrontendUserAuthentication();
        if ($authentication !== null) {
            return (string)($authentication->user[$propertyName] ?? '');
        }
        return '';
    }

    protected static function getFrontendUserAuthentication(): ?FrontendUserAuthentication
    {
        return $GLOBALS['TYPO3_REQUEST']?->getAttribute('frontend.user');
    }
}

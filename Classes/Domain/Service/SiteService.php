<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

class SiteService
{
    protected SiteFinder $siteFinder;

    public function __construct(SiteFinder $siteFinder)
    {
        $this->siteFinder = $siteFinder;
    }

    /**
     * @param int $languageId normally sys_language_uid
     * @param int $pageIdentifier pid
     * @return string "de" or "en"
     */
    public function getLanguageCodeFromLanguageAndPageIdentifier(int $languageId, int $pageIdentifier): string
    {
        $site = $this->getSiteFromPageIdentifier($pageIdentifier);
        return $this->getTwoLetterIsoCodeFromLanguageId($languageId, $site);
    }

    public function getLanguageCodeFromLanguageAndDomain(int $languageId, string $domain): string
    {
        $site = $this->getSiteFromDomain($domain);
        if ($site !== null) {
            return $this->getTwoLetterIsoCodeFromLanguageId($languageId, $site);
        }
        return '';
    }

    /**
     * Return first Site as default site (ordered alphabetical)
     *
     * @return Site
     */
    public function getDefaultSite(): Site
    {
        $sites = $this->siteFinder->getAllSites();
        return current($sites);
    }

    public function getSiteFromPageIdentifier(int $pageIdentifier): ?Site
    {
        try {
            return $this->siteFinder->getSiteByPageId($pageIdentifier);
        } catch (SiteNotFoundException $exception) {
            return null;
        }
    }

    public function getSiteIdentifierFromPageIdentifier(int $pageIdentifier): string
    {
        $site = $this->getSiteFromPageIdentifier($pageIdentifier);
        if ($site !== null) {
            return $site->getIdentifier();
        }
        return '';
    }

    public function getFirstDomain(): string
    {
        $site = self::getDefaultSite();
        return $site->getBase()->__toString();
    }

    public function getAllowedSites(): array
    {
        $sites = $this->siteFinder->getAllSites();
        if (BackendUtility::isAdministrator()) {
            return $sites;
        }

        $sanitziedSites = [];
        foreach ($sites as $site) {
            $beuserAuthentication = BackendUtility::getBackendUserAuthentication();
            if ($beuserAuthentication !== null) {
                $row = BackendUtilityCore::getRecord('pages', $site->getRootPageId());
                if ($beuserAuthentication->doesUserHaveAccess($row, Permission::PAGE_SHOW)) {
                    $sanitziedSites[$site->getIdentifier()] = $site;
                }
            }
        }
        return $sanitziedSites;
    }

    protected function getTwoLetterIsoCodeFromLanguageId(int $languageId, Site $site): string
    {
        foreach ($site->getLanguages() as $language) {
            if ($language->getLanguageId() === $languageId) {
                return $language->getTwoLetterIsoCode();
            }
        }
        return '';
    }

    protected function getSiteFromDomain(string $domain): ?Site
    {
        $sites = $this->siteFinder->getAllSites();
        /** @var Site $site */
        foreach ($sites as $site) {
            if ($domain === $site->getBase()->getHost()) {
                return $site;
            }
        }
        return null;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteService
{
    /**
     * @param int $languageId normally sys_language_uid
     * @param int $pageIdentifier pid
     * @return string "de" or "en"
     * @throws SiteNotFoundException
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

    public function getDefaultSite(): Site
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        return current($sites);
    }

    public function getSiteFromPageIdentifier(int $pageIdentifier): Site
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $siteFinder->getSiteByPageId($pageIdentifier);
    }

    public function getFirstDomain(): string
    {
        $site = self::getDefaultSite();
        return $site->getBase()->__toString();
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
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        /** @var Site $site */
        foreach ($sites as $site) {
            if ($domain === $site->getBase()->getHost()) {
                return $site;
            }
        }
        return null;
    }
}

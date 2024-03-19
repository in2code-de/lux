<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\StringUtility;
use In2code\Lux\Utility\UrlUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SiteService
 */
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

    /**
     * @param int $languageId
     * @param string $domain
     * @return string
     */
    public function getLanguageCodeFromLanguageAndDomain(int $languageId, string $domain): string
    {
        $site = $this->getSiteFromDomain($domain);
        if ($site !== null) {
            return $this->getTwoLetterIsoCodeFromLanguageId($languageId, $site);
        }
        return '';
    }

    /**
     * @return Site
     */
    public function getDefaultSite(): Site
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        return current($sites);
    }

    /**
     * @param int $pageIdentifier
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getSiteFromPageIdentifier(int $pageIdentifier): Site
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $siteFinder->getSiteByPageId($pageIdentifier);
    }

    /**
     * @return string
     */
    public function getFirstDomain(): string
    {
        $site = self::getDefaultSite();
        return $site->getBase()->__toString();
    }

    /**
     * Get all domains without protocol from all site configs
     *  [
     *      'www.domain.org',
     *      'stage.domain.org',
     *  ]
     *
     * @return array
     */
    public function getAllDomains(): array
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        $domains = [];
        foreach ($sites as $site) {
            $url = $site->getBase()->__toString();
            $url = UrlUtility::removeProtocolFromDomain($url);
            $domains[] = StringUtility::cleanString($url, true, './_-');
        }
        return $domains;
    }

    /**
     * Get all domains from all site configs for an sql query with regexp (splitted by |)
     *
     * @param bool $addCurrentDomain
     * @return string
     */
    public function getAllDomainsForWhereClause(bool $addCurrentDomain = true): string
    {
        $domains = $this->getAllDomains();
        if ($addCurrentDomain) {
            $domains = array_merge(
                $domains,
                [StringUtility::cleanString(FrontendUtility::getCurrentDomain(), true, './_-')]
            );
        }
        return implode('|', $domains);
    }

    /**
     * @param int $languageId
     * @param Site $site
     * @return string
     */
    protected function getTwoLetterIsoCodeFromLanguageId(int $languageId, Site $site): string
    {
        foreach ($site->getLanguages() as $language) {
            if ($language->getLanguageId() === $languageId) {
                return $language->getTwoLetterIsoCode();
            }
        }
        return '';
    }

    /**
     * @param string $domain
     * @return Site|null
     */
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

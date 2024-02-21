<?php

declare(strict_types=1);

namespace In2code\Lux\Update\AddSites;

use In2code\Lux\Domain\Service\SiteService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractSiteUpgrade
{
    /**
     * [
     *      123 => 'site 1',
     *      124 => 'site 2',
     * ]
     *
     * @var array
     */
    protected array $mapping = [];
    protected SiteService $siteService;

    public function __construct()
    {
        $this->siteService = GeneralUtility::makeInstance(SiteService::class);
    }

    protected function getSiteIdentifierFromPage(int $pageIdentifier): string
    {
        if (array_key_exists($pageIdentifier, $this->mapping)) {
            return $this->mapping[$pageIdentifier];
        }
        $site = $this->siteService->getSiteFromPageIdentifier($pageIdentifier);
        $siteIdentifier = $site->getIdentifier();
        $this->mapping[$pageIdentifier] = $siteIdentifier;
        return $siteIdentifier;
    }
}

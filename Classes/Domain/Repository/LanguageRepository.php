<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Service\SiteService;
use TYPO3\CMS\Core\SingletonInterface;

class LanguageRepository implements SingletonInterface
{
    /**
     * e.g.
     *  [
     *      0 => 'English',
     *      1 => 'Deutsch',
     *  ]
     *
     * @var array
     */
    protected array $languages = [];

    protected bool $allLanguagesSet = false;

    protected SiteService $siteService;

    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    public function getLabelToLanguageIdentifier(int $identifier): string
    {
        if (array_key_exists($identifier, $this->languages) === false) {
            $languages = $this->siteService->getDefaultSite()->getLanguages();
            foreach ($languages as $language) {
                if ($language->getLanguageId() === $identifier) {
                    $this->languages[$identifier] = $language->getTitle();
                }
            }
        }
        return $this->languages[$identifier] ?? 'undefined';
    }

    public function getAllLanguages(): array
    {
        if ($this->allLanguagesSet === false) {
            $languages = $this->siteService->getDefaultSite()->getLanguages();
            foreach ($languages as $language) {
                $this->languages[$language->getLanguageId()] = $language->getTitle();
            }
            $this->allLanguagesSet = true;
        }
        return $this->languages;
    }
}

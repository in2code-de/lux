<?php

declare(strict_types=1);
namespace In2code\Lux\Tests\Functional\Fixtures;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait SiteConfigurationTestTrait
{
    protected function writeSiteConfiguration(string $identifier, int $rootPageId): void
    {
        $configuration = [
            'rootPageId' => $rootPageId,
            'base' => 'https://' . $identifier . '.org/',
            'languages' => [
                [
                    'title' => 'English',
                    'enabled' => true,
                    'languageId' => 0,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                    'flag' => 'us',
                ],
            ],
        ];
        $path = Environment::getConfigPath() . '/sites/' . $identifier;
        GeneralUtility::mkdir_deep($path);
        GeneralUtility::writeFile($path . '/config.yaml', Yaml::dump($configuration, 99, 2), true);
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->getCache('core')->flush();
        $cacheManager->getCache('runtime')->flush();
    }
}

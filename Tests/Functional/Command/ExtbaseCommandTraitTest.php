<?php

declare(strict_types=1);

namespace In2code\Lux\Tests\Functional\Command;

use In2code\Lux\Command\ExtbaseCommandTrait;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Tests\Functional\Fixtures\Command\ExtbaseCommandAccessor;
use In2code\Lux\Tests\Functional\Fixtures\SiteConfigurationTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ExtbaseCommandTrait::class)]
#[CoversMethod(ExtbaseCommandTrait::class, 'initializeExtbase')]
class ExtbaseCommandTraitTest extends FunctionalTestCase
{
    use SiteConfigurationTestTrait;

    protected const ROOT_PAGE_ID_WITHOUT_LUX = 1;
    protected const ROOT_PAGE_ID_WITH_LUX = 2;
    protected const SETTINGS_PATH = 'commandControllers.summaryMail.fromEmail';

    protected array $testExtensionsToLoad = ['typo3conf/ext/lux'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Command/be_users.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Command/pages.csv');
        $this->setUpBackendUser(1);
        $this->setUpFrontendRootPage(self::ROOT_PAGE_ID_WITHOUT_LUX);
        $this->setUpFrontendRootPage(self::ROOT_PAGE_ID_WITH_LUX, [
            'constants' => ['EXT:lux/Configuration/TypoScript/constants.typoscript'],
            'setup' => ['EXT:lux/Configuration/TypoScript/setup.typoscript'],
        ]);
        $this->writeSiteConfiguration('site-without-lux', self::ROOT_PAGE_ID_WITHOUT_LUX);
        $this->writeSiteConfiguration('site-with-lux', self::ROOT_PAGE_ID_WITH_LUX);
    }

    public function testSettingsAreReadableFromTheSiteOfTheGivenRootPageId(): void
    {
        $this->getSubject()->initializeExtbase(self::ROOT_PAGE_ID_WITH_LUX);
        self::assertNotSame('', $this->getSummaryMailSender());
    }

    public function testSettingsAreNotReadableFromASiteWithoutStaticTypoScriptOfLux(): void
    {
        $this->getSubject()->initializeExtbase(self::ROOT_PAGE_ID_WITHOUT_LUX);
        self::assertSame('', $this->getSummaryMailSender());
    }

    public function testExceptionIsThrownForARootPageIdWithoutSite(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(1790121601);
        $this->getSubject()->initializeExtbase(4711);
    }

    protected function getSubject(): ExtbaseCommandAccessor
    {
        return new ExtbaseCommandAccessor('lux:test');
    }

    protected function getSummaryMailSender(): string
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        return (string)$configurationService->getTypoScriptSettingsByPath(self::SETTINGS_PATH);
    }
}

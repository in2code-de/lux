<?php

declare(strict_types=1);

namespace In2code\Lux\Tests\Unit\Domain\Service\Email;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Domain\Service\Email\SendSummaryService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Email\SendSummaryServiceFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(SendSummaryService::class)]
#[CoversMethod(SendSummaryService::class, '__construct')]
#[CoversMethod(SendSummaryService::class, 'getSummaryMailConfiguration')]
#[CoversMethod(SendSummaryService::class, 'getSender')]
#[CoversMethod(SendSummaryService::class, 'getSubject')]
#[CoversMethod(SendSummaryService::class, 'getMailTemplate')]
#[CoversMethod(SendSummaryService::class, 'checkProperties')]
class SendSummaryServiceTest extends UnitTestCase
{
    protected const MISSING_CONFIGURATION_EXCEPTION_CODE = 1789652586;

    protected const VALID_CONFIGURATION = [
        'fromEmail' => 'sender@domain.org',
        'fromName' => 'Sender Name',
        'subject' => 'Your lead summary',
        'mailTemplate' => 'EXT:lux/Resources/Private/Templates/Mail/SummaryMail.html',
    ];

    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    #[Test]
    public function testGetSummaryMailConfigurationReturnsConfiguration(): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION);
        self::assertSame(self::VALID_CONFIGURATION, $service->getSummaryMailConfigurationPublic());
    }

    #[Test]
    public function testGetSenderReturnsEmailAndName(): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION);
        self::assertSame(['sender@domain.org' => 'Sender Name'], $service->getSenderPublic());
    }

    #[Test]
    public function testGetSubjectReturnsSubject(): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION);
        self::assertSame('Your lead summary', $service->getSubjectPublic());
    }

    #[Test]
    public function testGetSubjectReturnsEmptyStringOnMissingSubject(): void
    {
        $configuration = self::VALID_CONFIGURATION;
        unset($configuration['subject']);
        $service = $this->getServiceFixture($configuration);
        self::assertSame('', $service->getSubjectPublic());
    }

    public static function missingConfigurationDataProvider(): array
    {
        return [
            'empty string from unresolvable typoscript path' => [''],
            'string instead of array' => ['summaryMail'],
            'null' => [null],
            'integer' => [0],
        ];
    }

    #[Test]
    #[DataProvider('missingConfigurationDataProvider')]
    public function testGetSummaryMailConfigurationThrowsExceptionOnMissingConfiguration(
        mixed $configuration
    ): void {
        $service = $this->getServiceFixture($configuration);
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(self::MISSING_CONFIGURATION_EXCEPTION_CODE);
        $service->getSummaryMailConfigurationPublic();
    }

    #[Test]
    public function testGetSummaryMailConfigurationExceptionMessageNamesTypoScriptPath(): void
    {
        $service = $this->getServiceFixture('');
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageMatches('~plugin\.tx_lux_fe\.settings\.commandControllers\.summaryMail~');
        $service->getSummaryMailConfigurationPublic();
    }

    #[Test]
    public function testGetSenderThrowsExceptionOnMissingConfiguration(): void
    {
        $service = $this->getServiceFixture('');
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(self::MISSING_CONFIGURATION_EXCEPTION_CODE);
        $service->getSenderPublic();
    }

    #[Test]
    public function testGetSubjectThrowsExceptionOnMissingConfiguration(): void
    {
        $service = $this->getServiceFixture('');
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(self::MISSING_CONFIGURATION_EXCEPTION_CODE);
        $service->getSubjectPublic();
    }

    #[Test]
    public function testGetMailTemplateThrowsExceptionOnMissingConfiguration(): void
    {
        $service = $this->getServiceFixture('');
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(self::MISSING_CONFIGURATION_EXCEPTION_CODE);
        $service->getMailTemplatePublic();
    }

    #[Test]
    public function testConfigurationIsAlwaysResolvedFromTheSameTypoScriptPath(): void
    {
        $configurationService = $this->createMock(ConfigurationService::class);
        $configurationService
            ->expects(self::atLeastOnce())
            ->method('getTypoScriptSettingsByPath')
            ->with('commandControllers.summaryMail')
            ->willReturn(self::VALID_CONFIGURATION);
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $configurationService);

        $service = new SendSummaryServiceFixture([new Visitor()]);
        $service->getSenderPublic();
        $service->getSubjectPublic();
    }

    #[Test]
    public function testCheckPropertiesThrowsExceptionOnEmptyEmails(): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION);
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(1524299754);
        $service->checkPropertiesPublic([]);
    }

    public static function invalidEmailDataProvider(): array
    {
        return [
            'no email at all' => ['receiver'],
            'missing domain' => ['receiver@'],
            'missing local part' => ['@domain.org'],
            'whitespace' => ['receiver @domain.org'],
        ];
    }

    #[Test]
    #[DataProvider('invalidEmailDataProvider')]
    public function testCheckPropertiesThrowsExceptionOnInvalidEmail(string $email): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION);
        $this->expectException(EmailValidationException::class);
        $this->expectExceptionCode(1524299869);
        $service->checkPropertiesPublic([$email]);
    }

    #[Test]
    public function testCheckPropertiesThrowsExceptionOnMissingVisitors(): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION, []);
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(1524300114);
        $service->checkPropertiesPublic(['receiver@domain.org']);
    }

    #[Test]
    public function testCheckPropertiesPassesWithValidProperties(): void
    {
        $service = $this->getServiceFixture(self::VALID_CONFIGURATION);
        $this->expectNotToPerformAssertions();
        $service->checkPropertiesPublic(['receiver@domain.org', 'receiver2@domain.org']);
    }

    protected function getServiceFixture(mixed $configuration, ?array $visitors = null): SendSummaryServiceFixture
    {
        $configurationService = self::createStub(ConfigurationService::class);
        $configurationService
            ->method('getTypoScriptSettingsByPath')
            ->willReturn($configuration);
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $configurationService);
        return new SendSummaryServiceFixture($visitors ?? [new Visitor()]);
    }
}

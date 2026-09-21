<?php

declare(strict_types=1);

namespace In2code\Lux\Tests\Functional\Domain\Service\Email;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\Email\SendSummaryService;
use In2code\Lux\Tests\Functional\Fixtures\Command\ExtbaseCommandAccessor;
use In2code\Lux\Tests\Functional\Fixtures\Domain\Service\Email\SendSummaryServiceAccessor;
use In2code\Lux\Tests\Functional\Fixtures\SiteConfigurationTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(SendSummaryService::class)]
class SendSummaryServiceTest extends FunctionalTestCase
{
    use SiteConfigurationTestTrait;

    protected const ROOT_PAGE_ID = 1;
    protected const FIXTURE_PATH = __DIR__ . '/../../../Fixtures/Domain/Service/Email/';

    protected array $testExtensionsToLoad = ['typo3conf/ext/lux'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(self::FIXTURE_PATH . 'be_users.csv');
        $this->importCSVDataSet(self::FIXTURE_PATH . 'pages.csv');
        $this->importCSVDataSet(self::FIXTURE_PATH . 'visitors.csv');
        $this->importCSVDataSet(self::FIXTURE_PATH . 'pagevisits.csv');
        $this->setUpBackendUser(1);
        $this->setUpFrontendRootPage(self::ROOT_PAGE_ID, [
            'constants' => ['EXT:lux/Configuration/TypoScript/constants.typoscript'],
            'setup' => ['EXT:lux/Configuration/TypoScript/setup.typoscript'],
        ]);
        $this->writeSiteConfiguration('summary-mail', self::ROOT_PAGE_ID);
        (new ExtbaseCommandAccessor('lux:test'))->initializeExtbase(self::ROOT_PAGE_ID);
    }

    /**
     * Pins the rendered values of the table so that resolving them in another way does not change the mail
     */
    public function testMailContainsFirstAndLastVisitedPageOfEveryLead(): void
    {
        $visitors = $this->getVisitors();
        $mail = (new SendSummaryServiceAccessor($visitors))->renderMailTemplate();
        self::assertStringContainsString('Contact (PID12)', $mail);
        self::assertStringContainsString('2026-01-03, 00:00', $mail);
        self::assertStringContainsString('Landingpage (PID10)', $mail);
        self::assertStringContainsString('2026-01-01, 00:00', $mail);
        self::assertStringContainsString('Google Organic', $mail);
    }

    public function testMailDoesNotLoadTheCompletePagevisitRelation(): void
    {
        $visitors = $this->getVisitors();
        (new SendSummaryServiceAccessor($visitors))->renderMailTemplate();
        self::assertFalse($this->isPagevisitRelationLoaded($visitors[0]));
    }

    /**
     * @return Visitor[]
     */
    protected function getVisitors(): array
    {
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        return [$visitorRepository->findByUid(1)];
    }

    protected function isPagevisitRelationLoaded(Visitor $visitor): bool
    {
        $property = new ReflectionProperty(Visitor::class, 'pagevisits');
        $pagevisits = $property->getValue($visitor);
        return $pagevisits instanceof LazyObjectStorage ? $pagevisits->isInitialized() : true;
    }
}

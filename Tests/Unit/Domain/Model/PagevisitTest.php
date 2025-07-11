<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\EnvironmentUtility;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\Pagevisit
 */
class PagevisitTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::getVisitor
     * @covers ::setVisitor
     */
    public function testVisitorGetterAndSetter(): void
    {
        $visitor = new Visitor();
        $pagevisit = new Pagevisit();
        $pagevisit->setVisitor($visitor);
        self::assertSame($visitor, $pagevisit->getVisitor());
    }

    /**
     * @covers ::getPage
     * @covers ::setPage
     */
    public function testPageGetterAndSetter(): void
    {
        $page = new Page();
        $pagevisit = new Pagevisit();
        $pagevisit->setPage($page);
        self::assertSame($page, $pagevisit->getPage());
    }

    /**
     * @covers ::getLanguage
     * @covers ::setLanguage
     */
    public function testLanguageGetterAndSetter(): void
    {
        $language = 2;
        $pagevisit = new Pagevisit();
        $pagevisit->setLanguage($language);
        self::assertSame($language, $pagevisit->getLanguage());
    }

    /**
     * @covers ::getCrdate
     * @covers ::setCrdate
     */
    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $pagevisit = new Pagevisit();
        $pagevisit->setCrdate($crdate);
        self::assertSame($crdate, $pagevisit->getCrdate());
    }

    /**
     * @covers ::getCrdate
     */
    public function testCrdateGetterWithoutSetting(): void
    {
        $pagevisit = new Pagevisit();
        self::assertInstanceOf(DateTime::class, $pagevisit->getCrdate());
    }

    /**
     * @covers ::getReferrer
     * @covers ::setReferrer
     */
    public function testReferrerGetterAndSetter(): void
    {
        $referrer = 'https://www.example.com';
        $pagevisit = new Pagevisit();
        $pagevisit->setReferrer($referrer);
        self::assertSame($referrer, $pagevisit->getReferrer());
    }

    /**
     * @covers ::isReferrerSet
     */
    public function testIsReferrerSet(): void
    {
        $pagevisit = new Pagevisit();
        self::assertFalse($pagevisit->isReferrerSet());

        $pagevisit->setReferrer('https://www.example.com');
        self::assertTrue($pagevisit->isReferrerSet());
    }

    /**
     * @covers ::getDomain
     * @covers ::setDomain
     */
    public function testDomainGetterAndSetter(): void
    {
        $domain = 'example.com';
        $pagevisit = new Pagevisit();
        $pagevisit->setDomain($domain);
        self::assertSame($domain, $pagevisit->getDomain());
    }

    /**
     * @covers ::getSite
     * @covers ::setSite
     */
    public function testSiteGetterAndSetter(): void
    {
        $site = 'example';
        $pagevisit = new Pagevisit();
        $pagevisit->setSite($site);
        self::assertSame($site, $pagevisit->getSite());
    }

    /**
     * @covers ::getReadableReferrer
     */
    public function testGetReadableReferrer(): void
    {
        $referrer = 'https://www.google.com';
        $readableReferrer = 'Google Organic';

        // Create a mock for Readable class
        $readableServiceMock = $this->getMockBuilder(Readable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readableServiceMock->expects(self::once())
            ->method('getReadableReferrer')
            ->willReturn($readableReferrer);

        // Mock GeneralUtility::makeInstance to return our mock
        GeneralUtility::addInstance(Readable::class, $readableServiceMock);

        $pagevisit = new Pagevisit();
        $pagevisit->setReferrer($referrer);

        self::assertSame($readableReferrer, $pagevisit->getReadableReferrer());
    }

    /**
     * @covers ::getAllPagevisits
     */
    public function testGetAllPagevisits(): void
    {
        $pagevisits = [new Pagevisit(), new Pagevisit()];

        // Create a mock for Visitor class
        $visitorMock = $this->getMockBuilder(Visitor::class)
            ->getMock();
        $visitorMock->expects(self::once())
            ->method('getPagevisits')
            ->willReturn($pagevisits);

        $pagevisit = new Pagevisit();
        $pagevisit->setVisitor($visitorMock);

        self::assertSame($pagevisits, $pagevisit->getAllPagevisits());
    }

    /**
     * @covers ::getNextPagevisit
     */
    public function testGetNextPagevisit(): void
    {
        $pagevisit1 = new Pagevisit();
        $pagevisit2 = new Pagevisit();
        $pagevisit3 = new Pagevisit();

        $pagevisits = [$pagevisit1, $pagevisit2, $pagevisit3];

        // Create a mock for Visitor class
        $visitorMock = $this->getMockBuilder(Visitor::class)
            ->getMock();
        $visitorMock->expects(self::once())
            ->method('getPagevisits')
            ->willReturn($pagevisits);

        $pagevisit1->setVisitor($visitorMock);

        self::assertSame($pagevisit2, $pagevisit1->getNextPagevisit());
    }

    /**
     * @covers ::getPreviousPagevisit
     */
    public function testGetPreviousPagevisit(): void
    {
        $pagevisit1 = new Pagevisit();
        $pagevisit2 = new Pagevisit();
        $pagevisit3 = new Pagevisit();

        $pagevisits = [$pagevisit1, $pagevisit2, $pagevisit3];

        // Create a mock for Visitor class
        $visitorMock = $this->getMockBuilder(Visitor::class)
            ->getMock();
        $visitorMock->expects(self::once())
            ->method('getPagevisits')
            ->willReturn($pagevisits);

        $pagevisit2->setVisitor($visitorMock);

        self::assertSame($pagevisit1, $pagevisit2->getPreviousPagevisit());
    }

    /**
     * @covers ::canBeRead
     */
    public function testCanBeReadWithEmptySite(): void
    {
        $pagevisit = new Pagevisit();
        // When site is empty, canBeRead should return true regardless of backend/admin status
        self::assertTrue($pagevisit->canBeRead());
    }

    /**
     * @covers ::getPageTitleWithLanguage
     */
    public function testGetPageTitleWithLanguageWithNullPage(): void
    {
        $pagevisit = new Pagevisit();
        self::assertSame('', $pagevisit->getPageTitleWithLanguage());
    }

    /**
     * @covers ::getPageTitleWithLanguage
     */
    public function testGetPageTitleWithLanguageWithDefaultLanguage(): void
    {
        $pageTitle = 'Test Page';
        $page = new Page();
        $page->setTitle($pageTitle);

        $pagevisit = new Pagevisit();
        $pagevisit->setPage($page);
        $pagevisit->setLanguage(0); // Default language

        self::assertSame($pageTitle, $pagevisit->getPageTitleWithLanguage());
    }

    /**
     * @covers ::getNewsvisit
     */
    public function testGetNewsvisitWhenNewsExtensionNotLoaded(): void
    {
        // This test assumes the news extension is not loaded
        // We can't easily mock the ExtensionManagementUtility::isLoaded static method
        // So we'll just test the method and expect it to return null

        $pagevisit = new Pagevisit();
        // This might fail if the news extension is actually loaded in the test environment
        // In that case, we would need a more sophisticated approach to mock the extension status
        self::assertNull($pagevisit->getNewsvisit());
    }
}

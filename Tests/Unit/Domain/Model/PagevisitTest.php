<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\Referrer\SourceHelper;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Pagevisit::class)]
#[CoversMethod(Pagevisit::class, 'canBeRead')]
#[CoversMethod(Pagevisit::class, 'getAllPagevisits')]
#[CoversMethod(Pagevisit::class, 'getCrdate')]
#[CoversMethod(Pagevisit::class, 'getDomain')]
#[CoversMethod(Pagevisit::class, 'getLanguage')]
#[CoversMethod(Pagevisit::class, 'getNewsvisit')]
#[CoversMethod(Pagevisit::class, 'getNextPagevisit')]
#[CoversMethod(Pagevisit::class, 'getPage')]
#[CoversMethod(Pagevisit::class, 'getPageTitleWithLanguage')]
#[CoversMethod(Pagevisit::class, 'getPreviousPagevisit')]
#[CoversMethod(Pagevisit::class, 'getReadableReferrer')]
#[CoversMethod(Pagevisit::class, 'getReferrer')]
#[CoversMethod(Pagevisit::class, 'getSite')]
#[CoversMethod(Pagevisit::class, 'getVisitor')]
#[CoversMethod(Pagevisit::class, 'isReferrerSet')]
#[CoversMethod(Pagevisit::class, 'setCrdate')]
#[CoversMethod(Pagevisit::class, 'setDomain')]
#[CoversMethod(Pagevisit::class, 'setLanguage')]
#[CoversMethod(Pagevisit::class, 'setPage')]
#[CoversMethod(Pagevisit::class, 'setReferrer')]
#[CoversMethod(Pagevisit::class, 'setSite')]
#[CoversMethod(Pagevisit::class, 'setVisitor')]
class PagevisitTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testVisitorGetterAndSetter(): void
    {
        $visitor = new Visitor();
        $pagevisit = new Pagevisit();
        $pagevisit->setVisitor($visitor);
        self::assertSame($visitor, $pagevisit->getVisitor());
    }

    public function testPageGetterAndSetter(): void
    {
        $page = new Page();
        $pagevisit = new Pagevisit();
        $pagevisit->setPage($page);
        self::assertSame($page, $pagevisit->getPage());
    }

    public function testLanguageGetterAndSetter(): void
    {
        $language = 2;
        $pagevisit = new Pagevisit();
        $pagevisit->setLanguage($language);
        self::assertSame($language, $pagevisit->getLanguage());
    }

    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $pagevisit = new Pagevisit();
        $pagevisit->setCrdate($crdate);
        self::assertSame($crdate, $pagevisit->getCrdate());
    }

    public function testCrdateGetterWithoutSetting(): void
    {
        $pagevisit = new Pagevisit();
        self::assertInstanceOf(DateTime::class, $pagevisit->getCrdate());
    }

    public function testReferrerGetterAndSetter(): void
    {
        $referrer = 'https://www.example.com';
        $pagevisit = new Pagevisit();
        $pagevisit->setReferrer($referrer);
        self::assertSame($referrer, $pagevisit->getReferrer());
    }

    public function testIsReferrerSet(): void
    {
        $pagevisit = new Pagevisit();
        self::assertFalse($pagevisit->isReferrerSet());

        $pagevisit->setReferrer('https://www.example.com');
        self::assertTrue($pagevisit->isReferrerSet());
    }

    public function testDomainGetterAndSetter(): void
    {
        $domain = 'example.com';
        $pagevisit = new Pagevisit();
        $pagevisit->setDomain($domain);
        self::assertSame($domain, $pagevisit->getDomain());
    }

    public function testSiteGetterAndSetter(): void
    {
        $site = 'example';
        $pagevisit = new Pagevisit();
        $pagevisit->setSite($site);
        self::assertSame($site, $pagevisit->getSite());
    }

    public function testGetReadableReferrer(): void
    {
        $referrer = 'https://www.google.com';
        $readableReferrer = 'Google Organic';

        // Create a mock for Readable class
        $readableServiceMock = $this->getMockBuilder(SourceHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readableServiceMock->expects(self::once())
            ->method('getReadableReferrer')
            ->willReturn($readableReferrer);

        // Mock GeneralUtility::makeInstance to return our mock
        GeneralUtility::addInstance(SourceHelper::class, $readableServiceMock);

        $pagevisit = new Pagevisit();
        $pagevisit->setReferrer($referrer);

        self::assertSame($readableReferrer, $pagevisit->getReadableReferrer());
    }

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

    public function testCanBeReadWithEmptySite(): void
    {
        $pagevisit = new Pagevisit();
        // When site is empty, canBeRead should return true regardless of backend/admin status
        self::assertTrue($pagevisit->canBeRead());
    }

    public function testGetPageTitleWithLanguageWithNullPage(): void
    {
        $pagevisit = new Pagevisit();
        self::assertSame('', $pagevisit->getPageTitleWithLanguage());
    }

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

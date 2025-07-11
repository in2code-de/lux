<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\Referrer\SourceHelper;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Utm::class)]
#[CoversMethod(Utm::class, 'getCrdate')]
#[CoversMethod(Utm::class, 'getNewsvisit')]
#[CoversMethod(Utm::class, 'getPagevisit')]
#[CoversMethod(Utm::class, 'getReadableReferrer')]
#[CoversMethod(Utm::class, 'getReferrer')]
#[CoversMethod(Utm::class, 'getUtmCampaign')]
#[CoversMethod(Utm::class, 'getUtmContent')]
#[CoversMethod(Utm::class, 'getUtmId')]
#[CoversMethod(Utm::class, 'getUtmMedium')]
#[CoversMethod(Utm::class, 'getUtmSource')]
#[CoversMethod(Utm::class, 'getUtmTerm')]
#[CoversMethod(Utm::class, 'getVisitor')]
#[CoversMethod(Utm::class, 'setCrdate')]
#[CoversMethod(Utm::class, 'setNewsvisit')]
#[CoversMethod(Utm::class, 'setPagevisit')]
#[CoversMethod(Utm::class, 'setReferrer')]
#[CoversMethod(Utm::class, 'setUtmCampaign')]
#[CoversMethod(Utm::class, 'setUtmContent')]
#[CoversMethod(Utm::class, 'setUtmId')]
#[CoversMethod(Utm::class, 'setUtmMedium')]
#[CoversMethod(Utm::class, 'setUtmSource')]
#[CoversMethod(Utm::class, 'setUtmTerm')]
class UtmTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testPagevisitGetterAndSetter(): void
    {
        $pagevisit = new Pagevisit();
        $utm = new Utm();
        $utm->setPagevisit($pagevisit);
        self::assertSame($pagevisit, $utm->getPagevisit());
    }

    public function testNewsvisitGetterAndSetter(): void
    {
        $newsvisit = new Newsvisit();
        $utm = new Utm();
        $utm->setNewsvisit($newsvisit);
        self::assertSame($newsvisit, $utm->getNewsvisit());
    }

    public function testUtmSourceGetterAndSetter(): void
    {
        $utmSource = 'google';
        $utm = new Utm();
        $utm->setUtmSource($utmSource);
        self::assertSame($utmSource, $utm->getUtmSource());
    }

    public function testUtmMediumGetterAndSetter(): void
    {
        $utmMedium = 'cpc';
        $utm = new Utm();
        $utm->setUtmMedium($utmMedium);
        self::assertSame($utmMedium, $utm->getUtmMedium());
    }

    public function testUtmCampaignGetterAndSetter(): void
    {
        $utmCampaign = 'spring_sale';
        $utm = new Utm();
        $utm->setUtmCampaign($utmCampaign);
        self::assertSame($utmCampaign, $utm->getUtmCampaign());
    }

    public function testUtmIdGetterAndSetter(): void
    {
        $utmId = 'abc123';
        $utm = new Utm();
        $utm->setUtmId($utmId);
        self::assertSame($utmId, $utm->getUtmId());
    }

    public function testUtmTermGetterAndSetter(): void
    {
        $utmTerm = 'running shoes';
        $utm = new Utm();
        $utm->setUtmTerm($utmTerm);
        self::assertSame($utmTerm, $utm->getUtmTerm());
    }

    public function testUtmContentGetterAndSetter(): void
    {
        $utmContent = 'logolink';
        $utm = new Utm();
        $utm->setUtmContent($utmContent);
        self::assertSame($utmContent, $utm->getUtmContent());
    }

    public function testReferrerGetterAndSetter(): void
    {
        $referrer = 'https://www.example.com';
        $utm = new Utm();
        $utm->setReferrer($referrer);
        self::assertSame($referrer, $utm->getReferrer());
    }

    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $utm = new Utm();
        $utm->setCrdate($crdate);
        self::assertSame($crdate, $utm->getCrdate());
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

        $utm = new Utm();
        $utm->setReferrer($referrer);

        self::assertSame($readableReferrer, $utm->getReadableReferrer());
    }

    public function testGetVisitorFromPagevisit(): void
    {
        $visitor = new Visitor();
        $pagevisit = new Pagevisit();
        $pagevisit->setVisitor($visitor);

        $utm = new Utm();
        $utm->setPagevisit($pagevisit);

        self::assertSame($visitor, $utm->getVisitor());
    }

    public function testGetVisitorFromNewsvisit(): void
    {
        $visitor = new Visitor();
        $newsvisit = new Newsvisit();
        $newsvisit->setVisitor($visitor);

        $utm = new Utm();
        $utm->setNewsvisit($newsvisit);

        self::assertSame($visitor, $utm->getVisitor());
    }

    public function testGetVisitorWithNoVisits(): void
    {
        $utm = new Utm();

        self::assertNull($utm->getVisitor());
    }
}

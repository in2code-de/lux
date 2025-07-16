<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\Utm
 */
class UtmTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::getPagevisit
     * @covers ::setPagevisit
     */
    public function testPagevisitGetterAndSetter(): void
    {
        $pagevisit = new Pagevisit();
        $utm = new Utm();
        $utm->setPagevisit($pagevisit);
        self::assertSame($pagevisit, $utm->getPagevisit());
    }

    /**
     * @covers ::getNewsvisit
     * @covers ::setNewsvisit
     */
    public function testNewsvisitGetterAndSetter(): void
    {
        $newsvisit = new Newsvisit();
        $utm = new Utm();
        $utm->setNewsvisit($newsvisit);
        self::assertSame($newsvisit, $utm->getNewsvisit());
    }

    /**
     * @covers ::getUtmSource
     * @covers ::setUtmSource
     */
    public function testUtmSourceGetterAndSetter(): void
    {
        $utmSource = 'google';
        $utm = new Utm();
        $utm->setUtmSource($utmSource);
        self::assertSame($utmSource, $utm->getUtmSource());
    }

    /**
     * @covers ::getUtmMedium
     * @covers ::setUtmMedium
     */
    public function testUtmMediumGetterAndSetter(): void
    {
        $utmMedium = 'cpc';
        $utm = new Utm();
        $utm->setUtmMedium($utmMedium);
        self::assertSame($utmMedium, $utm->getUtmMedium());
    }

    /**
     * @covers ::getUtmCampaign
     * @covers ::setUtmCampaign
     */
    public function testUtmCampaignGetterAndSetter(): void
    {
        $utmCampaign = 'spring_sale';
        $utm = new Utm();
        $utm->setUtmCampaign($utmCampaign);
        self::assertSame($utmCampaign, $utm->getUtmCampaign());
    }

    /**
     * @covers ::getUtmId
     * @covers ::setUtmId
     */
    public function testUtmIdGetterAndSetter(): void
    {
        $utmId = 'abc123';
        $utm = new Utm();
        $utm->setUtmId($utmId);
        self::assertSame($utmId, $utm->getUtmId());
    }

    /**
     * @covers ::getUtmTerm
     * @covers ::setUtmTerm
     */
    public function testUtmTermGetterAndSetter(): void
    {
        $utmTerm = 'running shoes';
        $utm = new Utm();
        $utm->setUtmTerm($utmTerm);
        self::assertSame($utmTerm, $utm->getUtmTerm());
    }

    /**
     * @covers ::getUtmContent
     * @covers ::setUtmContent
     */
    public function testUtmContentGetterAndSetter(): void
    {
        $utmContent = 'logolink';
        $utm = new Utm();
        $utm->setUtmContent($utmContent);
        self::assertSame($utmContent, $utm->getUtmContent());
    }

    /**
     * @covers ::getReferrer
     * @covers ::setReferrer
     */
    public function testReferrerGetterAndSetter(): void
    {
        $referrer = 'https://www.example.com';
        $utm = new Utm();
        $utm->setReferrer($referrer);
        self::assertSame($referrer, $utm->getReferrer());
    }

    /**
     * @covers ::getCrdate
     * @covers ::setCrdate
     */
    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $utm = new Utm();
        $utm->setCrdate($crdate);
        self::assertSame($crdate, $utm->getCrdate());
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

        $utm = new Utm();
        $utm->setReferrer($referrer);

        self::assertSame($readableReferrer, $utm->getReadableReferrer());
    }

    /**
     * @covers ::getVisitor
     */
    public function testGetVisitorFromPagevisit(): void
    {
        $visitor = new Visitor();
        $pagevisit = new Pagevisit();
        $pagevisit->setVisitor($visitor);

        $utm = new Utm();
        $utm->setPagevisit($pagevisit);

        self::assertSame($visitor, $utm->getVisitor());
    }

    /**
     * @covers ::getVisitor
     */
    public function testGetVisitorFromNewsvisit(): void
    {
        $visitor = new Visitor();
        $newsvisit = new Newsvisit();
        $newsvisit->setVisitor($visitor);

        $utm = new Utm();
        $utm->setNewsvisit($newsvisit);

        self::assertSame($visitor, $utm->getVisitor());
    }

    /**
     * @covers ::getVisitor
     */
    public function testGetVisitorWithNoVisits(): void
    {
        $utm = new Utm();

        self::assertNull($utm->getVisitor());
    }
}

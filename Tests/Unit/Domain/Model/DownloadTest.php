<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\File;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\Download
 */
class DownloadTest extends UnitTestCase
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
        $download = new Download();
        $download->setVisitor($visitor);
        self::assertSame($visitor, $download->getVisitor());
    }

    /**
     * @covers ::getCrdate
     * @covers ::setCrdate
     */
    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $download = new Download();
        $download->setCrdate($crdate);
        self::assertSame($crdate, $download->getCrdate());
    }

    /**
     * @covers ::getHref
     * @covers ::setHref
     */
    public function testHrefGetterAndSetter(): void
    {
        $href = 'https://example.com/file.pdf';
        $download = new Download();
        $download->setHref($href);
        self::assertSame($href, $download->getHref());
    }

    /**
     * @covers ::getPage
     * @covers ::setPage
     */
    public function testPageGetterAndSetter(): void
    {
        $page = new Page();
        $download = new Download();
        $download->setPage($page);
        self::assertSame($page, $download->getPage());
    }

    /**
     * @covers ::getFile
     * @covers ::setFile
     */
    public function testFileGetterAndSetter(): void
    {
        $file = new File();
        $download = new Download();
        $download->setFile($file);
        self::assertSame($file, $download->getFile());
    }

    /**
     * @covers ::getDomain
     * @covers ::setDomain
     */
    public function testDomainGetterAndSetter(): void
    {
        $domain = 'example.com';
        $download = new Download();
        $download->setDomain($domain);
        self::assertSame($domain, $download->getDomain());
    }

    /**
     * @covers ::getSite
     * @covers ::setSite
     */
    public function testSiteGetterAndSetter(): void
    {
        $site = 'example';
        $download = new Download();
        $download->setSite($site);
        self::assertSame($site, $download->getSite());
    }

    /**
     * @covers ::setPage
     */
    public function testSetPageWithNull(): void
    {
        $download = new Download();
        $download->setPage(null);
        self::assertNull($download->getPage());
    }
}

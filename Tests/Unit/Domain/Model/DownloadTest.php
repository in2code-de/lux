<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\File;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Download::class)]
#[CoversMethod(Download::class, 'getCrdate')]
#[CoversMethod(Download::class, 'getDomain')]
#[CoversMethod(Download::class, 'getFile')]
#[CoversMethod(Download::class, 'getHref')]
#[CoversMethod(Download::class, 'getPage')]
#[CoversMethod(Download::class, 'getSite')]
#[CoversMethod(Download::class, 'getVisitor')]
#[CoversMethod(Download::class, 'setCrdate')]
#[CoversMethod(Download::class, 'setDomain')]
#[CoversMethod(Download::class, 'setFile')]
#[CoversMethod(Download::class, 'setHref')]
#[CoversMethod(Download::class, 'setPage')]
#[CoversMethod(Download::class, 'setSite')]
#[CoversMethod(Download::class, 'setVisitor')]
class DownloadTest extends UnitTestCase
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
        $download = new Download();
        $download->setVisitor($visitor);
        self::assertSame($visitor, $download->getVisitor());
    }

    public function testCrdateGetterAndSetter(): void
    {
        $crdate = new DateTime('2023-01-01');
        $download = new Download();
        $download->setCrdate($crdate);
        self::assertSame($crdate, $download->getCrdate());
    }

    public function testHrefGetterAndSetter(): void
    {
        $href = 'https://example.com/file.pdf';
        $download = new Download();
        $download->setHref($href);
        self::assertSame($href, $download->getHref());
    }

    public function testPageGetterAndSetter(): void
    {
        $page = new Page();
        $download = new Download();
        $download->setPage($page);
        self::assertSame($page, $download->getPage());
    }

    public function testFileGetterAndSetter(): void
    {
        $file = new File();
        $download = new Download();
        $download->setFile($file);
        self::assertSame($file, $download->getFile());
    }

    public function testDomainGetterAndSetter(): void
    {
        $domain = 'example.com';
        $download = new Download();
        $download->setDomain($domain);
        self::assertSame($domain, $download->getDomain());
    }

    public function testSiteGetterAndSetter(): void
    {
        $site = 'example';
        $download = new Download();
        $download->setSite($site);
        self::assertSame($site, $download->getSite());
    }

    public function testSetPageWithNull(): void
    {
        $download = new Download();
        $download->setPage(null);
        self::assertNull($download->getPage());
    }
}

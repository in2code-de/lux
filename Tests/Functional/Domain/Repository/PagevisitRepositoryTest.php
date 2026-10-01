<?php

declare(strict_types=1);

namespace In2code\Lux\Tests\Functional\Domain\Repository;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(PagevisitRepository::class)]
#[CoversMethod(PagevisitRepository::class, 'findOneByVisitor')]
class PagevisitRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/lux'];

    protected PagevisitRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Domain/Repository/pagevisits.csv');
        $this->subject = GeneralUtility::makeInstance(PagevisitRepository::class);
    }

    public function testFindOneByVisitorAscReturnsOldestVisit(): void
    {
        $pagevisit = $this->subject->findOneByVisitor($this->getVisitor(1), QueryInterface::ORDER_ASCENDING);
        self::assertSame('Landingpage', $pagevisit->getPage()->getTitle());
        self::assertSame('https://www.google.com/', $pagevisit->getReferrer());
        self::assertSame('Google Organic', $pagevisit->getReadableReferrer());
        self::assertSame('2026-01-01', $pagevisit->getCrdate()->format('Y-m-d'));
    }

    public function testFindOneByVisitorDescReturnsNewestVisit(): void
    {
        $pagevisit = $this->subject->findOneByVisitor($this->getVisitor(1), QueryInterface::ORDER_DESCENDING);
        self::assertSame('Contact', $pagevisit->getPage()->getTitle());
        self::assertSame('2026-01-03', $pagevisit->getCrdate()->format('Y-m-d'));
    }

    public function testDeletedPagevisitsAreIgnored(): void
    {
        self::assertSame('Contact', $this->subject->findOneByVisitor($this->getVisitor(1), QueryInterface::ORDER_DESCENDING)->getPage()->getTitle());
    }

    public function testVisitorWithASinglePagevisitGetsItAsFirstAndLastOne(): void
    {
        $visitor = $this->getVisitor(2);
        self::assertSame('Products', $this->subject->findOneByVisitor($visitor, QueryInterface::ORDER_ASCENDING)->getPage()->getTitle());
        self::assertSame('Products', $this->subject->findOneByVisitor($visitor, QueryInterface::ORDER_DESCENDING)->getPage()->getTitle());
    }

    public function testVisitorWithoutPagevisitsReturnsNull(): void
    {
        self::assertNull($this->subject->findOneByVisitor($this->getVisitor(3), QueryInterface::ORDER_ASCENDING));
        self::assertNull($this->subject->findOneByVisitor($this->getVisitor(3), QueryInterface::ORDER_DESCENDING));
    }

    public function testNotPersistedVisitorReturnsNull(): void
    {
        self::assertNull($this->subject->findOneByVisitor(new Visitor(), QueryInterface::ORDER_ASCENDING));
        self::assertNull($this->subject->findOneByVisitor(new Visitor(), QueryInterface::ORDER_DESCENDING));
    }

    protected function getVisitor(int $identifier): Visitor
    {
        return GeneralUtility::makeInstance(VisitorRepository::class)->findByUid($identifier);
    }
}

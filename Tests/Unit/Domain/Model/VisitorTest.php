<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Visitor::class)]
#[CoversMethod(Visitor::class, 'getCategoryscoringsSortedByScoring')]
#[CoversMethod(Visitor::class, 'getFullName')]
#[CoversMethod(Visitor::class, 'getNameCombination')]
#[CoversMethod(Visitor::class, 'getLastPagevisit')]
#[CoversMethod(Visitor::class, 'getPagevisitFirst')]
#[CoversMethod(Visitor::class, 'getPagevisitLast')]
#[CoversMethod(Visitor::class, 'setPagevisitFirst')]
#[CoversMethod(Visitor::class, 'setPagevisitLast')]
#[CoversMethod(Visitor::class, 'getNumberOfUniquePagevisits')]
class VisitorTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public static function getFullNameDataProvider(): array
    {
        return [
            [
                'firstname',
                'lastname',
                'email@mail.org',
                'lastname, firstname',
            ],
            [
                'firstname',
                'lastname',
                '',
                'lastname, firstname [notIdentified]',
            ],
            [
                '',
                'lastname',
                '',
                'lastname [notIdentified]',
            ],
            [
                'firstname',
                '',
                'email@mail.org',
                'firstname',
            ],
            [
                '',
                'lastname',
                'email@mail.org',
                'lastname',
            ],
            [
                '',
                '',
                '',
                'anonym [d41d8c]',
            ],
        ];
    }

    #[DataProvider('getFullNameDataProvider')]
    public function testGetFullName(string $firstname, string $lastname, string $email, string $expectedResult): void
    {
        $visitor = new Visitor();
        $attributeA = new Attribute();
        $attributeA->setName('lastname')->setValue($lastname);
        $visitor->addAttribute($attributeA);
        $attributeB = new Attribute();
        $attributeB->setName('firstname')->setValue($firstname);
        $visitor->addAttribute($attributeB);
        if (!empty($email)) {
            $visitor->setEmail($email);
            $visitor->setIdentified(true);
        }
        self::assertSame($expectedResult, $visitor->getFullName());
    }

    public static function getCategoryscoringsSortedByScoringDataProvider(): array
    {
        return [
            [
                [2, 1, 3],
                [3, 2, 1],
            ],
            [
                [20, 100, 0],
                [100, 20, 0],
            ],
            [
                [10, 20, 30, 20],
                [30, 20, 20, 10],
            ],
        ];
    }

    #[DataProvider('getCategoryscoringsSortedByScoringDataProvider')]
    public function testGetCategoryscoringsSortedByScoring(array $sortings, array $expectedSortings): void
    {
        $objectStorage = new ObjectStorage();
        foreach ($sortings as $scoring) {
            $categoryscoring = new Categoryscoring();
            $categoryscoring->setScoring($scoring);
            $objectStorage->attach($categoryscoring);
        }
        $visitor = new Visitor();
        $visitor->setCategoryscorings($objectStorage);

        $csSorted = $visitor->getCategoryscoringsSortedByScoring();
        $newScoringArray = [];
        foreach ($csSorted as $cs) {
            $newScoringArray[] = $cs->getScoring();
        }
        self::assertSame($expectedSortings, $newScoringArray);
    }

    #[Test]
    public function getLastPagevisitReturnsLastVisit(): void
    {
        $visitor = new Visitor();
        $pagevisit = new Pagevisit();

        $pagevisit->setCrdate(new DateTime('2026-01-01 10:00:00'));
        $visitor->addPagevisit($pagevisit);

        $newerPagevisit = new Pagevisit();
        $newerPagevisit->setCrdate(new DateTime('2026-01-02 10:00:00'));
        $visitor->addPagevisit($newerPagevisit);

        self::assertSame($newerPagevisit, $visitor->getLastPagevisit());
    }

    #[Test]
    public function setPagevisitLastPreventsLoadingOfTheRelation(): void
    {
        $visitor = new Visitor();
        $relatedPagevisit = new Pagevisit();
        $relatedPagevisit->setCrdate(new DateTime('2026-01-01 10:00:00'));
        $visitor->addPagevisit($relatedPagevisit);

        $resolvedPagevisit = new Pagevisit();
        $resolvedPagevisit->setCrdate(new DateTime('2026-01-09 10:00:00'));
        $visitor->setPagevisitLast($resolvedPagevisit);

        self::assertSame($resolvedPagevisit, $visitor->getPagevisitLast());
        self::assertSame($resolvedPagevisit, $visitor->getLastPagevisit());
    }

    #[Test]
    public function setPagevisitFirstPreventsLoadingOfTheRelation(): void
    {
        $visitor = new Visitor();
        $relatedPagevisit = new Pagevisit();
        $relatedPagevisit->setCrdate(new DateTime('2026-01-01 10:00:00'));
        $visitor->addPagevisit($relatedPagevisit);

        $resolvedPagevisit = new Pagevisit();
        $resolvedPagevisit->setCrdate(new DateTime('2025-12-24 10:00:00'));
        $visitor->setPagevisitFirst($resolvedPagevisit);

        self::assertSame($resolvedPagevisit, $visitor->getPagevisitFirst());
    }

    #[Test]
    public function pagevisitsAreNotSharedBetweenVisitors(): void
    {
        $firstVisitor = new Visitor();
        $firstPagevisit = new Pagevisit();
        $firstVisitor->setPagevisitLast($firstPagevisit);

        $secondVisitor = new Visitor();
        $secondPagevisit = new Pagevisit();
        $secondPagevisit->setCrdate(new DateTime('2026-01-01 10:00:00'));
        $secondVisitor->addPagevisit($secondPagevisit);

        self::assertSame($firstPagevisit, $firstVisitor->getPagevisitLast());
        self::assertSame($secondPagevisit, $secondVisitor->getPagevisitLast());
    }

    #[Test]
    public function getLastPagevisitCachesResultPerVisitor(): void
    {
        $visitor = new Visitor();
        $pagevisit = new Pagevisit();
        $pagevisit->setCrdate(new DateTime('2026-01-01 10:00:00'));
        $visitor->addPagevisit($pagevisit);

        self::assertSame($pagevisit, $visitor->getLastPagevisit());

        $newerPagevisit = new Pagevisit();
        $newerPagevisit->setCrdate(new DateTime('2026-01-02 10:00:00'));
        $visitor->addPagevisit($newerPagevisit);

        self::assertSame($pagevisit, $visitor->getLastPagevisit());
    }

    #[Test]
    public function getLastPagevisitIsNotSharedBetweenVisitors(): void
    {
        $firstPagevisit = new Pagevisit();
        $firstVisitor = new Visitor();
        $firstVisitor->addPagevisit($firstPagevisit);

        $secondPagevisit = new Pagevisit();
        $secondVisitor = new Visitor();
        $secondVisitor->addPagevisit($secondPagevisit);

        self::assertSame($firstPagevisit, $firstVisitor->getLastPagevisit());
        self::assertSame($secondPagevisit, $secondVisitor->getLastPagevisit());
    }

    public static function getNumberOfUniquePagevisitsDataProvider(): array
    {
        return [
            'one visit' => [
                'expectedNumber' => 1,
                'pageVisitTimestamps' => ['2026-09-22 10:23']
            ],
            'two visits within an hour' => [
                'expectedNumber' => 1,
                'pageVisitTimestamps' => ['2026-09-22 10:23', '2026-09-22 10:53']
            ],
            'two visits more than an hour' => [
                'expectedNumber' => 2,
                'pageVisitTimestamps' => ['2026-09-22 10:23', '2026-09-22 11:23']
            ],
            'two visits next day same time' => [
                'expectedNumber' => 2,
                'pageVisitTimestamps' => ['2026-09-22 10:23', '2026-09-23 10:23']
            ],
            'two visits other day other time' => [
                'expectedNumber' => 2,
                'pageVisitTimestamps' => ['2026-09-22 10:23', '2026-10-23 12:57']
            ],
            'multiple visits mixed time' => [
                'expectedNumber' => 4,
                'pageVisitTimestamps' => [
                    '2026-09-22 10:23',
                    '2026-09-22 10:53',
                    '2026-09-22 12:57',
                    '2026-09-22 14:57',
                    '2026-09-22 15:00',
                    '2026-09-27 15:00',
                ]
            ],
            'visits after the given time are filtered' => [
                'expectedNumber' => 3,
                'pageVisitTimestamps' => [
                    '2026-09-22 10:23',
                    '2026-09-22 15:00',
                    '2026-09-22 17:00',
                    '2026-09-27 15:00',
                    '2026-09-28 15:00',
                ],
                'until' => new DateTime('2026-09-23 00:00')
            ],
            'visit exactly at the given time is counted' => [
                'expectedNumber' => 1,
                'pageVisitTimestamps' => [
                    '2026-09-22 10:23',
                    '2026-09-27 15:00',
                ],
                'until' => new DateTime('2026-09-22 10:23')
            ],
            'unsorted visits are counted like sorted ones' => [
                'expectedNumber' => 4,
                'pageVisitTimestamps' => [
                    '2026-09-27 15:00',
                    '2026-09-22 15:00',
                    '2026-09-22 10:53',
                    '2026-09-22 14:57',
                    '2026-09-22 12:57',
                    '2026-09-22 10:23',
                ]
            ],
        ];
    }

    #[Test]
    #[DataProvider('getNumberOfUniquePagevisitsDataProvider')]
    public function testGetNumberOfUniquePagevisits(
        int $expectedNumber,
        array $pageVisitTimestamps,
        ?DateTime $until = null
    ): void {
        $visitor = new Visitor();
        foreach ($pageVisitTimestamps as $pageVisitTimestamp) {
            $pageVisit = new Pagevisit();
            $pageVisit->setCrdate(new DateTime($pageVisitTimestamp));
            $visitor->addPagevisit($pageVisit);
        }

        self::assertSame($expectedNumber, $visitor->getNumberOfUniquePagevisits($until));
    }
}

<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ScoringService;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Domain\Service\ScoringServiceFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ScoringService::class)]
#[CoversMethod(ScoringService::class, 'calculateScoring')]
#[CoversMethod(ScoringService::class, 'calculateAndSetScoring')]
class ScoringServiceTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public static function calculateScoringDataProvider(): array
    {
        return [
            [
                1,
                2,
                3,
                4,
                61,
            ],
            [
                0,
                0,
                1,
                1,
                11,
            ],
            [
                47,
                872,
                45,
                2,
                133,
            ],
            [
                0,
                0,
                0,
                0,
                0,
            ],
            [
                0,
                100,
                0,
                0,
                0,
            ],
        ];
    }

    #[DataProvider('calculateScoringDataProvider')]
    public function testCalculateScoring(int $nod, int $nodslv, int $nov, int $nosv, int $expected): void
    {
        $scoringService = new ScoringServiceFixture();
        $scoringService->numberOfDownloads = $nod;
        $scoringService->numberOfDaysSinceLastVisit = $nodslv;
        $scoringService->numberOfVisits = $nov;
        $scoringService->numberOfSiteVisits = $nosv;
        $visitor = new Visitor();
        self::assertSame($expected, $scoringService->calculateScoring($visitor));
        $visitor->setBlacklisted(true);
        self::assertSame(0, $scoringService->calculateScoring($visitor));
    }
}

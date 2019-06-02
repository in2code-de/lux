<?php
namespace In2code\Lux\Tests\Domain\Model;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use In2code\Lux\Tests\Unit\Fixtures\Domain\Service\ScoringServiceFixture;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class BackendUserUtilityTest
 * @coversDefaultClass \In2code\Lux\Domain\Service\ScoringService
 */
class ScoringServiceTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     */
    public function setUp()
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return array
     */
    public function calculateScoringDataProvider()
    {
        return [
            [
                1,
                2,
                3,
                4,
                61
            ],
            [
                0,
                0,
                1,
                1,
                11
            ],
            [
                47,
                872,
                45,
                2,
                133
            ],
            [
                0,
                0,
                0,
                0,
                0
            ]
        ];
    }

    /**
     * @param int $nod
     * @param int $nodslv
     * @param int $nov
     * @param int $nosv
     * @param int $expected
     * @return void
     * @dataProvider calculateScoringDataProvider
     * @covers ::calculateScoring
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidQueryException#
     */
    public function testCalculateScoring(int $nod, int $nodslv, int $nov, int $nosv, int $expected)
    {
        $scoringService = new ScoringServiceFixture();
        $scoringService->numberOfDownloads = $nod;
        $scoringService->numberOfDaysSinceLastVisit = $nodslv;
        $scoringService->numberOfVisits = $nov;
        $scoringService->numberOfSiteVisits = $nosv;
        $visitor = new Visitor();
        $this->assertSame($expected, $scoringService->calculateScoring($visitor));
        $visitor->setBlacklisted(true);
        $this->assertSame(0, $scoringService->calculateScoring($visitor));
    }
}

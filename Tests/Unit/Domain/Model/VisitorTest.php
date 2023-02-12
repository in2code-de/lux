<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class BackendUserUtilityTest
 * @coversDefaultClass \In2code\Lux\Domain\Model\Visitor
 */
class VisitorTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     */
    public function setUp(): void
    {
        TestingHelper::setDefaultConstants();
    }

    /**
     * @return array
     */
    public function getFullNameDataProvider()
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

    /**
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $expectedResult
     * @return void
     * @dataProvider getFullNameDataProvider
     * @covers ::getFullName
     * @covers ::getNameCombination
     */
    public function testGetFullName(string $firstname, string $lastname, string $email, string $expectedResult)
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

    /**
     * @return array
     */
    public function getCategoryscoringsSortedByScoringDataProvider()
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

    /**
     * @return void
     * @dataProvider getCategoryscoringsSortedByScoringDataProvider
     * @covers ::getCategoryscoringsSortedByScoring
     */
    public function testGetCategoryscoringsSortedByScoring(array $sortings, array $expectedSortings)
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
}

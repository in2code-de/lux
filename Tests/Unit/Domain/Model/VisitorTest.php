<?php
namespace In2code\Lux\Tests\Domain\Model;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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
    public function setUp()
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
                'lastname, firstname'
            ],
            [
                'firstname',
                'lastname',
                '',
                'lastname, firstname [notIdentified]'
            ],
            [
                '',
                'lastname',
                '',
                'lastname [notIdentified]'
            ],
            [
                'firstname',
                '',
                'email@mail.org',
                'firstname'
            ],
            [
                '',
                'lastname',
                'email@mail.org',
                'lastname'
            ],
            [
                '',
                '',
                '',
                'anonym'
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
        $this->assertSame($expectedResult, $visitor->getFullName());
    }
}

<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\Attribute
 */
class AttributeTest extends UnitTestCase
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
        $attribute = new Attribute();
        $attribute->setVisitor($visitor);
        self::assertSame($visitor, $attribute->getVisitor());
    }

    /**
     * @covers ::getName
     * @covers ::setName
     */
    public function testNameGetterAndSetter(): void
    {
        $name = 'firstname';
        $attribute = new Attribute();
        $attribute->setName($name);
        self::assertSame($name, $attribute->getName());
    }

    /**
     * @covers ::getValue
     * @covers ::setValue
     */
    public function testValueGetterAndSetter(): void
    {
        $value = 'John';
        $attribute = new Attribute();
        $attribute->setValue($value);
        self::assertSame($value, $attribute->getValue());
    }

    /**
     * @covers ::isEmail
     */
    public function testIsEmail(): void
    {
        $attribute = new Attribute();

        // Test with non-email attribute
        $attribute->setName('firstname');
        self::assertFalse($attribute->isEmail());

        // Test with email attribute
        $attribute->setName(Attribute::KEY_NAME);
        self::assertTrue($attribute->isEmail());
    }
}

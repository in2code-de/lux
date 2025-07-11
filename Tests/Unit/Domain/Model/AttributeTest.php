<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Attribute::class)]
#[CoversMethod(Attribute::class, 'getName')]
#[CoversMethod(Attribute::class, 'getValue')]
#[CoversMethod(Attribute::class, 'getVisitor')]
#[CoversMethod(Attribute::class, 'isEmail')]
#[CoversMethod(Attribute::class, 'setName')]
#[CoversMethod(Attribute::class, 'setValue')]
#[CoversMethod(Attribute::class, 'setVisitor')]
class AttributeTest extends UnitTestCase
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
        $attribute = new Attribute();
        $attribute->setVisitor($visitor);
        self::assertSame($visitor, $attribute->getVisitor());
    }

    public function testNameGetterAndSetter(): void
    {
        $name = 'firstname';
        $attribute = new Attribute();
        $attribute->setName($name);
        self::assertSame($name, $attribute->getName());
    }

    public function testValueGetterAndSetter(): void
    {
        $value = 'John';
        $attribute = new Attribute();
        $attribute->setValue($value);
        self::assertSame($value, $attribute->getValue());
    }

    public function testIsEmail(): void
    {
        $attribute = new Attribute();

        // Test with a non-email attribute
        $attribute->setName('firstname');
        self::assertFalse($attribute->isEmail());

        // Test with an email attribute
        $attribute->setName(Attribute::KEY_NAME);
        self::assertTrue($attribute->isEmail());
    }
}

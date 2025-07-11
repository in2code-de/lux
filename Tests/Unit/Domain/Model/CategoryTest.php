<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Category::class)]
#[CoversMethod(Category::class, 'getTitle')]
#[CoversMethod(Category::class, 'isLuxCategory')]
#[CoversMethod(Category::class, 'isLuxCategoryCompany')]
#[CoversMethod(Category::class, 'setLuxCategory')]
#[CoversMethod(Category::class, 'setLuxCategoryCompany')]
#[CoversMethod(Category::class, 'setTitle')]
class CategoryTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testTitleGetterAndSetter(): void
    {
        $title = 'Test Category';
        $category = new Category();
        $category->setTitle($title);
        self::assertSame($title, $category->getTitle());
    }

    public function testLuxCategoryGetterAndSetter(): void
    {
        $category = new Category();

        // Default value should be false
        self::assertFalse($category->isLuxCategory());

        // Set to true
        $category->setLuxCategory(true);
        self::assertTrue($category->isLuxCategory());

        // Set back to false
        $category->setLuxCategory(false);
        self::assertFalse($category->isLuxCategory());
    }

    public function testLuxCategoryCompanyGetterAndSetter(): void
    {
        $category = new Category();

        // Default value should be false
        self::assertFalse($category->isLuxCategoryCompany());

        // Set to true
        $category->setLuxCategoryCompany(true);
        self::assertTrue($category->isLuxCategoryCompany());

        // Set back to false
        $category->setLuxCategoryCompany(false);
        self::assertFalse($category->isLuxCategoryCompany());
    }
}

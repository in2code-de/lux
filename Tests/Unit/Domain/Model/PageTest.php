<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Page::class)]
#[CoversMethod(Page::class, '__construct')]
#[CoversMethod(Page::class, 'getCategories')]
#[CoversMethod(Page::class, 'getLuxCategories')]
#[CoversMethod(Page::class, 'getTitle')]
class PageTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testGetCategories(): void
    {
        $page = new Page();
        self::assertInstanceOf(ObjectStorage::class, $page->getCategories());
        self::assertCount(0, $page->getCategories());
    }

    public function testGetTitle(): void
    {
        $page = new Page();
        // The title property is protected and there's no setter method,
        // so we can only test the default value
        self::assertSame('', $page->getTitle());
    }

    public function testGetLuxCategories(): void
    {
        $page = new Page();

        // Create categories
        $category1 = new Category();
        $category1->setTitle('Category 1');
        $category1->setLuxCategory(true);

        $category2 = new Category();
        $category2->setTitle('Category 2');
        $category2->setLuxCategory(false);

        $category3 = new Category();
        $category3->setTitle('Category 3');
        $category3->setLuxCategory(true);

        // Add categories to page
        $page->getCategories()->attach($category1);
        $page->getCategories()->attach($category2);
        $page->getCategories()->attach($category3);

        // Test getLuxCategories
        $luxCategories = $page->getLuxCategories();
        self::assertCount(2, $luxCategories);
        self::assertContains($category1, $luxCategories);
        self::assertContains($category3, $luxCategories);
        self::assertNotContains($category2, $luxCategories);
    }

    public function testGetLuxCategoriesWithNoLuxCategories(): void
    {
        $page = new Page();

        // Create categories
        $category1 = new Category();
        $category1->setTitle('Category 1');
        $category1->setLuxCategory(false);

        $category2 = new Category();
        $category2->setTitle('Category 2');
        $category2->setLuxCategory(false);

        // Add categories to page
        $page->getCategories()->attach($category1);
        $page->getCategories()->attach($category2);

        // Test getLuxCategories
        $luxCategories = $page->getLuxCategories();
        self::assertCount(0, $luxCategories);
    }

    public function testGetLuxCategoriesWithNoCategories(): void
    {
        $page = new Page();

        // Test getLuxCategories
        $luxCategories = $page->getLuxCategories();
        self::assertCount(0, $luxCategories);
    }
}

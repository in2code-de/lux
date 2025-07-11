<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Model\Metadata;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\Metadata
 */
class MetadataTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::__construct
     * @covers ::getCategories
     */
    public function testGetCategories(): void
    {
        $metadata = new Metadata();
        self::assertInstanceOf(ObjectStorage::class, $metadata->getCategories());
        self::assertCount(0, $metadata->getCategories());
    }

    /**
     * @covers ::getLuxCategories
     */
    public function testGetLuxCategories(): void
    {
        $metadata = new Metadata();

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

        // Add categories to metadata
        $metadata->getCategories()->attach($category1);
        $metadata->getCategories()->attach($category2);
        $metadata->getCategories()->attach($category3);

        // Test getLuxCategories
        $luxCategories = $metadata->getLuxCategories();
        self::assertCount(2, $luxCategories);
        self::assertContains($category1, $luxCategories);
        self::assertContains($category3, $luxCategories);
        self::assertNotContains($category2, $luxCategories);
    }

    /**
     * @covers ::getLuxCategories
     */
    public function testGetLuxCategoriesWithNoLuxCategories(): void
    {
        $metadata = new Metadata();

        // Create categories
        $category1 = new Category();
        $category1->setTitle('Category 1');
        $category1->setLuxCategory(false);

        $category2 = new Category();
        $category2->setTitle('Category 2');
        $category2->setLuxCategory(false);

        // Add categories to metadata
        $metadata->getCategories()->attach($category1);
        $metadata->getCategories()->attach($category2);

        // Test getLuxCategories
        $luxCategories = $metadata->getLuxCategories();
        self::assertCount(0, $luxCategories);
    }

    /**
     * @covers ::getLuxCategories
     */
    public function testGetLuxCategoriesWithNoCategories(): void
    {
        $metadata = new Metadata();

        // Test getLuxCategories
        $luxCategories = $metadata->getLuxCategories();
        self::assertCount(0, $luxCategories);
    }
}

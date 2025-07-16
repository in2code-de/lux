<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\File;
use In2code\Lux\Domain\Model\Metadata;
use In2code\Lux\Tests\Helper\TestingHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Domain\Model\File
 */
class FileTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::getName
     * @covers ::setName
     */
    public function testNameGetterAndSetter(): void
    {
        $name = 'example.pdf';
        $file = new File();
        $file->setName($name);
        self::assertSame($name, $file->getName());
    }

    /**
     * @covers ::getMetadata
     * @covers ::setMetadata
     */
    public function testMetadataGetterAndSetter(): void
    {
        $metadata = new Metadata();
        $file = new File();
        $file->setMetadata($metadata);
        self::assertSame($metadata, $file->getMetadata());
    }
}

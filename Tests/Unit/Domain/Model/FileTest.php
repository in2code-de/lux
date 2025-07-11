<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\File;
use In2code\Lux\Domain\Model\Metadata;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(File::class)]
#[CoversMethod(File::class, 'getMetadata')]
#[CoversMethod(File::class, 'getName')]
#[CoversMethod(File::class, 'setMetadata')]
#[CoversMethod(File::class, 'setName')]
class FileTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testNameGetterAndSetter(): void
    {
        $name = 'example.pdf';
        $file = new File();
        $file->setName($name);
        self::assertSame($name, $file->getName());
    }

    public function testMetadataGetterAndSetter(): void
    {
        $metadata = new Metadata();
        $file = new File();
        $file->setMetadata($metadata);
        self::assertSame($metadata, $file->getMetadata());
    }
}

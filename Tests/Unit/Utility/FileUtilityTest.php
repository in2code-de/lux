<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\FileUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(FileUtility::class)]
#[CoversMethod(FileUtility::class, 'getFilenameFromPathAndFilename')]
class FileUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetFilenameFromPathAndFilename(): void
    {
        $pathAndFilename = 'fileadmin/folder/file123.pdf';
        self::assertSame('file123.pdf', FileUtility::getFilenameFromPathAndFilename($pathAndFilename));
    }
}

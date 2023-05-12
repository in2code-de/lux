<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\FileUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Utility\FileUtility
 */
class FileUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getFilenameFromPathAndFilename
     */
    public function testGetFilenameFromPathAndFilename(): void
    {
        $pathAndFilename = 'fileadmin/folder/file123.pdf';
        self::assertSame('file123.pdf', FileUtility::getFilenameFromPathAndFilename($pathAndFilename));
    }
}

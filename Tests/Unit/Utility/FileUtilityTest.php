<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\FileUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Lux\Utility\FileUtility
 */
class FileUtilityTest extends UnitTestCase
{
    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getFilenameFromPathAndFilename
     */
    public function testGetFilenameFromPathAndFilename()
    {
        $pathAndFilename = 'fileadmin/folder/file123.pdf';
        self::assertSame('file123.pdf', FileUtility::getFilenameFromPathAndFilename($pathAndFilename));
    }
}

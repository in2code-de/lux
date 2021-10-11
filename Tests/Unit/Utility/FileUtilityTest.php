<?php
namespace In2code\Lux\Tests\Unit\Utility;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use In2code\Lux\Utility\FileUtility;

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
        $this->assertSame('file123.pdf', FileUtility::getFilenameFromPathAndFilename($pathAndFilename));
    }
}

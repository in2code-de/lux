<?php
namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\UrlUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Lux\Utility\UrlUtility
 */
class UrlUtilityTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $testFilesToDelete = [];

    /**
     * @return array
     */
    public function convertToRelativeDataProvider()
    {
        return [
            [
                '/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://domain.org'
            ],
            [
                'fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://domain.org'
            ],
            [
                'https://domain.org/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://domain.org'
            ],
            [
                '/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://webserver-selfhosting-support.localhost.de'
            ],
            [
                'fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://webserver-selfhosting-support.localhost.de'
            ],
            [
                'https://webserver-selfhosting-support.localhost.de/fileadmin/file.pdf',
                'fileadmin/file.pdf',
                'https://webserver-selfhosting-support.localhost.de'
            ]
        ];
    }

    /**
     * @param string $givenPath
     * @param string $expectedResult
     * @param string $domain
     * @return void
     * @dataProvider convertToRelativeDataProvider
     * @covers ::convertToRelative
     */
    public function testConvertToRelative(string $givenPath, string $expectedResult, string $domain)
    {
        $this->assertEquals($expectedResult, UrlUtility::convertToRelative($givenPath, $domain));
    }
}

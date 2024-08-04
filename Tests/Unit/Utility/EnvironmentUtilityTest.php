<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\EnvironmentUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Lux\Utility\EnvironmentUtility
 */
class EnvironmentUtilityTest extends UnitTestCase
{
    /**
     * @return void
     * @covers ::isFrontend
     */
    public function testIsFrontend(): void
    {
        self::assertFalse(EnvironmentUtility::isFrontend());
    }

    /**
     * @return void
     * @covers ::isBackend
     */
    public function testIsBackend(): void
    {
        self::assertFalse(EnvironmentUtility::isBackend());
    }

    /**
     * @return void
     * @covers ::isCli
     */
    public function testIsCli(): void
    {
        self::assertTrue(EnvironmentUtility::isCli());
    }

    /**
     * @return void
     * @covers ::isComposerMode
     */
    public function testIsComposerMode(): void
    {
        self::assertTrue(EnvironmentUtility::isComposerMode());
    }
}

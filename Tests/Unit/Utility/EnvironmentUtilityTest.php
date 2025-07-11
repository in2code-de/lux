<?php

namespace In2code\Lux\Tests\Unit\Utility;

use In2code\Lux\Utility\EnvironmentUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(EnvironmentUtility::class)]
#[CoversMethod(EnvironmentUtility::class, 'isBackend')]
#[CoversMethod(EnvironmentUtility::class, 'isCli')]
#[CoversMethod(EnvironmentUtility::class, 'isComposerMode')]
#[CoversMethod(EnvironmentUtility::class, 'isFrontend')]
class EnvironmentUtilityTest extends UnitTestCase
{
    public function testIsFrontend(): void
    {
        self::assertFalse(EnvironmentUtility::isFrontend());
    }

    public function testIsBackend(): void
    {
        self::assertFalse(EnvironmentUtility::isBackend());
    }

    public function testIsCli(): void
    {
        self::assertTrue(EnvironmentUtility::isCli());
    }

    public function testIsComposerMode(): void
    {
        self::assertTrue(EnvironmentUtility::isComposerMode());
    }
}

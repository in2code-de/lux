<?php

namespace In2code\Lux\Tests\Unit\Domain\Model;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Fingerprint::class)]
#[CoversMethod(Fingerprint::class, 'setValue')]
class FingerprintTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public function testFingerprintType(): void
    {
        $fingerprint = new Fingerprint();

        $fingerprint->setValue(random_bytes(32));
        self::assertEquals($fingerprint->getType(), Fingerprint::TYPE_FINGERPRINT);

        $fingerprint->setValue(random_bytes(33));
        self::assertEquals($fingerprint->getType(), Fingerprint::TYPE_STORAGE);
    }

    public function testAssertSameHashesWithSameIps(): void
    {
        $fingerprint = new Fingerprint();
        $identifier = bin2hex(random_bytes(16));

        $fingerprint->setValue($identifier);
        $value1 = $fingerprint->getValue();

        $fingerprint->setValue($identifier);
        $value2 = $fingerprint->getValue();

        self::assertEquals($value1, $value2);
    }

    public function testAssertDifferentHashesWithDifferentIps(): void
    {
        $fingerprint = new Fingerprint();
        $identifier = random_bytes(32);

        GeneralUtility::setIndpEnv('REMOTE_ADDR', '192.168.178.1');
        $fingerprint->setValue($identifier);

        $value1 = $fingerprint->getValue();

        GeneralUtility::setIndpEnv('REMOTE_ADDR', '192.168.178.16');
        $fingerprint->setValue($identifier);

        $value2 = $fingerprint->getValue();

        self::assertNotEquals($value1, $value2);
    }

    public function testAssertNoHashingForStorageType(): void
    {
        $fingerprint = new Fingerprint();
        $identifier = random_bytes(33);

        $fingerprint->setValue($identifier);
        $value = $fingerprint->getValue();

        self::assertEquals($identifier, $value);
    }
}

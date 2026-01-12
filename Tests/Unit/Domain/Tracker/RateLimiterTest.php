<?php

namespace In2code\Lux\Tests\Unit\Domain\Tracker;

use In2code\Lux\Domain\Cache\RateLimiterCache;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Tracker\RateLimiter;
use In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent;
use In2code\Lux\Exception\RateLimitException;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(RateLimiter::class)]
class RateLimiterTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    #[Test]
    public function itAllowsRequestsUnderLimit(): void
    {
        // Mock cache to return count below limit
        $cacheMock = $this->createMock(RateLimiterCache::class);
        $cacheMock->expects(self::once())
            ->method('incrementAndGet')
            ->with('test1234567890abcdef1234567890ab1234567890abcdef1234567890abcdef')
            ->willReturn(10); // 10 < 20 (default limit)

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects(self::never())
            ->method('warning'); // Should not log when under limit

        $rateLimiter = new RateLimiter($cacheMock, $loggerMock);

        // Create real fingerprint and event objects (they are final)
        $fingerprint = $this->createFingerprintWithValue('test1234567890abcdef1234567890ab1234567890abcdef1234567890abcdef');
        $event = new StopAnyProcessBeforePersistenceEvent($fingerprint);

        // Should not throw exception
        $rateLimiter->__invoke($event);

        // If we reach here, the test passes
        self::assertTrue(true);
    }

    #[Test]
    public function itBlocksRequestsOverLimit(): void
    {
        // Mock cache to return count over limit
        $cacheMock = $this->createMock(RateLimiterCache::class);
        $cacheMock->expects(self::once())
            ->method('incrementAndGet')
            ->with('test1234567890abcdef1234567890ab1234567890abcdef1234567890abcdef')
            ->willReturn(21); // 21 > 20 (default limit)

        $loggerMock = $this->createMock(LoggerInterface::class);

        $rateLimiter = new RateLimiter($cacheMock, $loggerMock);

        // Create real fingerprint and event objects (they are final)
        $fingerprint = $this->createFingerprintWithValue('test1234567890abcdef1234567890ab1234567890abcdef1234567890abcdef');
        $event = new StopAnyProcessBeforePersistenceEvent($fingerprint);

        // Should throw exception
        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');
        $this->expectExceptionCode(1768214806);

        $rateLimiter->__invoke($event);
    }

    #[Test]
    public function itHandlesEmptyFingerprint(): void
    {
        // Mock cache should not be called
        $cacheMock = $this->createMock(RateLimiterCache::class);
        $cacheMock->expects(self::never())
            ->method('incrementAndGet');

        $loggerMock = $this->createMock(LoggerInterface::class);

        $rateLimiter = new RateLimiter($cacheMock, $loggerMock);

        // Create fingerprint with empty value
        $fingerprint = new Fingerprint('example.com', 'TestUA');
        // Note: value is empty by default since we don't call setValue()
        $event = new StopAnyProcessBeforePersistenceEvent($fingerprint);

        // Should return early without exception
        $rateLimiter->__invoke($event);

        // If we reach here, the test passes
        self::assertTrue(true);
    }

    #[Test]
    public function itIncrementsCounterOnEachRequest(): void
    {
        // Mock cache to verify incrementAndGet is called
        $cacheMock = $this->createMock(RateLimiterCache::class);
        $cacheMock->expects(self::once())
            ->method('incrementAndGet')
            ->with('test1234567890abcdef1234567890ab1234567890abcdef1234567890abcdef')
            ->willReturn(1); // First request

        $loggerMock = $this->createMock(LoggerInterface::class);

        $rateLimiter = new RateLimiter($cacheMock, $loggerMock);

        // Create real fingerprint and event objects (they are final)
        $fingerprint = $this->createFingerprintWithValue('test1234567890abcdef1234567890ab1234567890abcdef1234567890abcdef');
        $event = new StopAnyProcessBeforePersistenceEvent($fingerprint);

        $rateLimiter->__invoke($event);

        // Test passes if incrementAndGet was called
        self::assertTrue(true);
    }

    /**
     * Helper method to create a Fingerprint with a specific value
     * Uses reflection to set the protected value property
     *
     * @param string $value
     * @return Fingerprint
     */
    protected function createFingerprintWithValue(string $value): Fingerprint
    {
        $fingerprint = new Fingerprint('example.com', 'TestUA');
        $reflection = new \ReflectionClass($fingerprint);
        $property = $reflection->getProperty('value');
        $property->setAccessible(true);
        $property->setValue($fingerprint, $value);
        return $fingerprint;
    }
}

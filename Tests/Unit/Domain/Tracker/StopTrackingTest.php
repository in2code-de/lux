<?php

namespace In2code\Lux\Tests\Unit\Domain\Tracker;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Service\CrawlerDetectionService;
use In2code\Lux\Domain\Tracker\StopTracking;
use In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent;
use In2code\Lux\Exception\DisallowedUserAgentException;
use In2code\Lux\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(StopTracking::class)]
class StopTrackingTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    #[Test]
    public function itStopsTrackingOnEmptyUserAgent(): void
    {
        $crawlerDetectionService = $this->createMock(CrawlerDetectionService::class);
        $crawlerDetectionService->expects(self::never())->method('isCrawler');
        $stopTracking = new StopTracking($crawlerDetectionService);

        $this->expectException(DisallowedUserAgentException::class);
        $this->expectExceptionCode(1592581081);

        $stopTracking->__invoke(new StopAnyProcessBeforePersistenceEvent(new Fingerprint('example.com', '')));
    }

    #[Test]
    public function itStopsTrackingOnDetectedCrawler(): void
    {
        $userAgent = 'Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)';
        $crawlerDetectionService = $this->createMock(CrawlerDetectionService::class);
        $crawlerDetectionService->expects(self::once())
            ->method('isCrawler')
            ->with($userAgent, [])
            ->willReturn(true);
        $stopTracking = new StopTracking($crawlerDetectionService);

        $this->expectException(DisallowedUserAgentException::class);
        $this->expectExceptionCode(1592581260);

        $stopTracking->__invoke(
            new StopAnyProcessBeforePersistenceEvent(new Fingerprint('example.com', $userAgent))
        );
    }

    #[Test]
    public function itAllowsTrackingForHumanUserAgent(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/126.0.0.0 Safari/537.36';
        $crawlerDetectionService = $this->createMock(CrawlerDetectionService::class);
        $crawlerDetectionService->expects(self::once())
            ->method('isCrawler')
            ->with($userAgent, [])
            ->willReturn(false);
        $stopTracking = new StopTracking($crawlerDetectionService);

        $stopTracking->__invoke(
            new StopAnyProcessBeforePersistenceEvent(new Fingerprint('example.com', $userAgent))
        );

        self::assertTrue(true);
    }

    #[Test]
    public function itStopsTrackingOnBlacklistedBrowser(): void
    {
        $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $crawlerDetectionService = self::createStub(CrawlerDetectionService::class);
        $crawlerDetectionService->method('isCrawler')->willReturn(false);
        $stopTracking = new StopTracking($crawlerDetectionService);

        $this->expectException(DisallowedUserAgentException::class);
        $this->expectExceptionCode(1565604005);

        $stopTracking->__invoke(
            new StopAnyProcessBeforePersistenceEvent(new Fingerprint('example.com', $userAgent))
        );
    }
}

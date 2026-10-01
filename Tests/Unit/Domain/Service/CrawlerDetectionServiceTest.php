<?php

namespace In2code\Lux\Tests\Unit\Domain\Service;

use In2code\Lux\Domain\Service\CrawlerDetectionService;
use In2code\Lux\Events\DetectCrawlerEvent;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CrawlerDetectionService::class)]
class CrawlerDetectionServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public static function isCrawlerReturnsBoolDataProvider(): array
    {
        return [
            'empty user agent' => ['', false],
            'chrome on windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
                . 'Chrome/126.0.0.0 Safari/537.36',
                false,
            ],
            'firefox on macos' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',
                false,
            ],
            'safari on iphone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) '
                . 'Version/17.5 Mobile/15E148 Safari/604.1',
                false,
            ],
            'googlebot' => [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                true,
            ],
            'gptbot' => [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; GPTBot/1.2; '
                . '+https://openai.com/gptbot',
                true,
            ],
            'claudebot' => [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; '
                . '+claudebot@anthropic.com)',
                true,
            ],
            'perplexitybot' => [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; PerplexityBot/1.0; '
                . '+https://perplexity.ai/perplexitybot)',
                true,
            ],
            'bytespider' => [
                'Mozilla/5.0 (compatible; Bytespider; spider-feedback@bytedance.com)',
                true,
            ],
            'semrushbot' => [
                'Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)',
                true,
            ],
            'headless chrome' => [
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) '
                . 'HeadlessChrome/126.0.0.0 Safari/537.36',
                true,
            ],
            'lighthouse' => [
                'Mozilla/5.0 (Linux; Android 11) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126 '
                . 'Mobile Safari/537.36 Chrome-Lighthouse',
                true,
            ],
            'curl' => ['curl/8.4.0', true],
            'wget' => ['Wget/1.21.4', true],
            'python requests' => ['python-requests/2.31.0', true],
            'whatsapp' => ['WhatsApp/2.23.20.0 A', true],
            'sistrix' => ['Mozilla/5.0 (compatible; SISTRIX Crawler; http://crawler.sistrix.net/)', true],
        ];
    }

    #[Test]
    #[DataProvider('isCrawlerReturnsBoolDataProvider')]
    public function isCrawlerReturnsBool(string $userAgent, bool $expectedResult): void
    {
        self::assertSame($expectedResult, $this->getSubject()->isCrawler($userAgent));
    }

    #[Test]
    public function isCrawlerRespectsAdditionalUserAgentParts(): void
    {
        $subject = $this->getSubject();
        $userAgent = 'MyOwnHouseAgent/1.0';

        self::assertFalse($subject->isCrawler($userAgent));
        self::assertTrue($subject->isCrawler($userAgent, ['myownhouseagent']));
    }

    #[Test]
    public function isCrawlerIgnoresEmptyAdditionalUserAgentParts(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/126.0.0.0 Safari/537.36';

        self::assertFalse($this->getSubject()->isCrawler($userAgent, ['', ' ']));
    }

    #[Test]
    public function isCrawlerCanBeOverwrittenByEvent(): void
    {
        $eventDispatcher = self::createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(
            function (DetectCrawlerEvent $event): DetectCrawlerEvent {
                return $event->setCrawler($event->isCrawler() === false);
            }
        );
        $subject = new CrawlerDetectionService(new CrawlerDetect(), $eventDispatcher);

        self::assertFalse($subject->isCrawler('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'));
        self::assertTrue($subject->isCrawler('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0'));
    }

    protected function getSubject(): CrawlerDetectionService
    {
        $eventDispatcher = self::createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnArgument(0);
        return new CrawlerDetectionService(new CrawlerDetect(), $eventDispatcher);
    }
}

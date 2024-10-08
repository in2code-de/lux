<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Factory\UtmFactory;
use In2code\Lux\Domain\Model\Utm;
use In2code\Lux\Domain\Repository\UtmRepository;
use In2code\Lux\Domain\Service\LogService;
use In2code\Lux\Events\Log\UtmEvent;
use In2code\Lux\Events\NewsTrackerEvent;
use In2code\Lux\Events\PageTrackerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UtmTracker
{
    protected ?UtmRepository $utmRepository = null;
    protected ?LogService $logService = null;
    protected ?UtmFactory $utmFactory = null;
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function __construct(
        UtmRepository $utmRepository,
        UtmFactory $utmFactory,
        LogService $logService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->utmRepository = $utmRepository;
        $this->utmFactory = $utmFactory;
        $this->logService = $logService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function trackPage(PageTrackerEvent $event): void
    {
        if ($this->isAnyUtmParameterGiven() && $this->isNewsDetailPage() === false) {
            $utm = $this->getUtm($event->getArguments());
            $utm->setPagevisit($event->getPagevisit());
            try {
                $this->utmRepository->add($utm);
                $this->utmRepository->persistAll();
                $this->eventDispatcher->dispatch(new UtmEvent($utm));
            } catch (Throwable $exception) {
                // Do nothing
            }
        }
    }

    public function trackNews(NewsTrackerEvent $event): void
    {
        if ($this->isAnyUtmParameterGiven()) {
            $utm = $this->getUtm($event->getArguments());
            $utm->setNewsvisit($event->getNewsvisit());
            try {
                $this->utmRepository->add($utm);
                $this->utmRepository->persistAll();
                $this->eventDispatcher->dispatch(new UtmEvent($utm));
            } catch (Throwable $exception) {
                // Do nothing
            }
        }
    }

    protected function getUtm(array $arguments): Utm
    {
        $parameters = [];
        foreach ($this->utmFactory->getUtmKeys() as $key => $keys) {
            foreach ($keys as $keySub) {
                if (isset($parameters[$key]) === false) {
                    $parameters[$key] = $this->getArgumentFromCurrentUrl($keySub);
                }
            }
        }
        return $this->utmFactory->get($parameters, $arguments['referrer'] ?? '');
    }

    protected function isNewsDetailPage(): bool
    {
        $news = $this->getArgumentFromCurrentUrl('tx_news_pi1');
        return ($news['news'] ?? 0) > 0;
    }

    protected function isAnyUtmParameterGiven(): bool
    {
        $arguments = $this->getArgumentsFromCurrentUrl();
        foreach (array_keys($arguments) as $key) {
            if (in_array($key, $this->utmFactory->getAllUtmKeys())) {
                return true;
            }
        }
        return false;
    }

    final protected function getArguments(): array
    {
        return $_REQUEST['tx_lux_fe'] ?? [];
    }

    final protected function getCurrentUrl(): string
    {
        $arguments = $this->getArguments();
        if (!empty($arguments['arguments']['currentUrl'])) {
            return $arguments['arguments']['currentUrl'];
        }
        return '';
    }

    final protected function getArgumentsFromCurrentUrl(): array
    {
        $parts = parse_url($this->getCurrentUrl());
        if (isset($parts['query'])) {
            $query = $parts['query'];
            parse_str($query, $arguments);
            return $arguments;
        }
        return [];
    }

    final protected function getArgumentFromCurrentUrl(string $argumentName)
    {
        $arguments = $this->getArgumentsFromCurrentUrl();
        if (array_key_exists($argumentName, $arguments)) {
            return $arguments[$argumentName];
        }
        return null;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Events\AfterTrackingEvent;
use Throwable;
use TYPO3\CMS\Core\Utility\ArrayUtility;

abstract class AbstractFinisher implements FinisherInterface
{
    /**
     * Overwrite this property if you want that your finisher will not be started by every start action
     *
     * @var array
     */
    protected array $startWithControllerActions = [
        'pageRequestAction',
        'fieldListeningRequestAction',
        'formListeningRequestAction',
        'email4LinkRequestAction',
        'downloadRequestAction',
    ];

    protected ?Visitor $visitor = null;
    protected AfterTrackingEvent $event;

    /**
     * Finisher configuration from TypoScript setup
     *
     * @var array
     */
    protected array $configuration = [];

    public function __construct(AfterTrackingEvent $event, array $configuration)
    {
        $this->event = $event;
        $this->visitor = $event->getVisitor();
        $this->configuration = $configuration;
    }

    /**
     * Extend with your own logic
     *
     * @return bool
     */
    public function shouldFinisherRun(): bool
    {
        return true;
    }

    public function handle(): void
    {
        if ($this->shouldFinisherRun() === true
            && in_array($this->event->getActionMethodName(), $this->startWithControllerActions)) {
            $this->event->addResult($this->start());
        }
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getEvent(): AfterTrackingEvent
    {
        return $this->event;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    final protected function getConfigurationByKey(string $key): string
    {
        $value = '';
        if (array_key_exists($key, $this->getConfiguration())) {
            $value = $this->getConfiguration()[$key];
        }
        return $value;
    }

    final protected function getConfigurationByPath(string $path)
    {
        $configuration = $this->getConfiguration();
        try {
            return ArrayUtility::getValueByPath($configuration, $path, '.');
        } catch (Throwable $exception) {
            unset($exception);
        }
        return '';
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Finisher;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Events\AfterTrackingEvent;
use Throwable;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class AbstractFinisher
 */
abstract class AbstractFinisher implements FinisherInterface
{
    /**
     * Overwrite this property if you want that your finisher will not be started by every start action
     *
     * @var array
     */
    protected $startWithControllerActions = [
        'pageRequestAction',
        'fieldListeningRequestAction',
        'formListeningRequestAction',
        'email4LinkRequestAction',
        'downloadRequestAction',
    ];

    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * @var AfterTrackingEvent
     */
    protected $event;

    /**
     * Finisher configuration from TypoScript setup
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * @param AfterTrackingEvent $event
     * @param array $configuration
     */
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

    /**
     * @return void
     */
    public function handle(): void
    {
        if ($this->shouldFinisherRun() === true
            && in_array($this->event->getActionMethodName(), $this->startWithControllerActions)) {
            $this->event->addResult($this->start());
        }
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return AfterTrackingEvent
     */
    public function getEvent(): AfterTrackingEvent
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param string $key
     * @return string
     */
    final protected function getConfigurationByKey(string $key): string
    {
        $value = '';
        if (array_key_exists($key, $this->getConfiguration())) {
            $value = $this->getConfiguration()[$key];
        }
        return $value;
    }

    /**
     * @param string $path
     * @return mixed
     */
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

<?php
declare(strict_types = 1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Visitor;

final class AfterTrackingEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * Possible actions are:
     *  "pageRequestAction"
     *  "fieldListeningRequestAction"
     *  "formListeningRequestAction"
     *  "email4LinkRequestAction"
     *  "downloadRequestAction"
     *  "error"
     *
     * @var string
     */
    protected $actionMethodName = '';

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * JSON result for AJAX request
     *
     * @var array
     */
    protected $results = [];

    /**
     * @param Visitor $visitor
     * @param string $actionMethodName
     * @param array $arguments
     */
    public function __construct(Visitor $visitor, string $actionMethodName, array $arguments = [])
    {
        $this->visitor = $visitor;
        $this->actionMethodName = $actionMethodName;
        $this->arguments = $arguments;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return string
     */
    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getArgument(string $name)
    {
        $arguments = $this->getArguments();
        if (array_key_exists($name, $arguments)) {
            return $arguments[$name];
        }
        return null;
    }

    /**
     * @param array $results
     * @return AfterTrackingEvent
     */
    public function setResults(array $results): AfterTrackingEvent
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @param array $result
     * @return AfterTrackingEvent
     */
    public function addResult(array $result): AfterTrackingEvent
    {
        if ($result !== []) {
            $this->results = array_merge([$result], $this->getResults());
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}

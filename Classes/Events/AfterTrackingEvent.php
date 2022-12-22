<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Visitor;

final class AfterTrackingEvent
{
    protected Visitor $visitor;

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
    protected string $actionMethodName = '';

    protected array $arguments = [];

    /**
     * JSON result for AJAX request
     *
     * @var array
     */
    protected array $results = [];

    public function __construct(Visitor $visitor, string $actionMethodName, array $arguments = [])
    {
        $this->visitor = $visitor;
        $this->actionMethodName = $actionMethodName;
        $this->arguments = $arguments;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

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

    public function setResults(array $results): AfterTrackingEvent
    {
        $this->results = $results;
        return $this;
    }

    public function addResult(array $result): AfterTrackingEvent
    {
        if ($result !== []) {
            $this->results = array_merge([$result], $this->getResults());
        }
        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Finisher;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class AbstractFinisher
 */
abstract class AbstractFinisher implements FinisherInterface
{
    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * What was the start action for calling this finisher?
     *
     * Possible actions are:
     *  "pageRequestAction"
     *  "fieldListeningRequestAction"
     *  "formListeningRequestAction"
     *  "email4LinkRequestAction"
     *  "downloadRequestAction"
     *
     * @var string
     */
    protected $controllerAction = '';

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
        'downloadRequestAction'
    ];

    /**
     * Former return values of the signal - e.g. actions from EXT:luxenterprise for JavaScript (AJAX)
     *
     * @var array
     */
    protected $actions = [];

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * Finisher configuration from TypoScript setup
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * AbstractFinisher constructor.
     * @param Visitor $visitor
     * @param string $controllerAction
     * @param array $actions
     * @param array $parameters
     * @param array $configuration
     */
    public function __construct(
        Visitor $visitor,
        string $controllerAction,
        array $actions,
        array $parameters,
        array $configuration
    ) {
        $this->visitor = $visitor;
        $this->controllerAction = $controllerAction;
        $this->actions = $actions;
        $this->parameters = $parameters;
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
     * @return array
     */
    public function handle(): array
    {
        if ($this->shouldFinisherRun() === true
            && in_array($this->controllerAction, $this->startWithControllerActions)) {
            return array_merge([$this->start()], $this->getActions());
        }
        return $this->getActions();
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
    public function getControllerAction(): string
    {
        return $this->controllerAction;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
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
        } catch (\Exception $exception) {
            unset($exception);
        }
        return '';
    }
}

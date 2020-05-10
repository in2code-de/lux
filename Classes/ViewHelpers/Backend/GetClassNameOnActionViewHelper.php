<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetClassNameOnActionViewHelper
 */
class GetClassNameOnActionViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('onAction', 'string', 'Given action name', true);
        $this->registerArgument('view', 'array', 'view.controller and view.action', true);
        $this->registerArgument('className', 'string', 'Classname to return if action fits', false, ' btn-primary');
        $this->registerArgument('fallbackClassName', 'string', 'Classname for another action', false, ' btn-default');
    }

    /**
     * Return className if onAction string fits to current action
     *
     * @return string
     */
    public function render(): string
    {
        if (strtolower($this->arguments['view']['action']) === strtolower($this->arguments['onAction'])) {
            return $this->arguments['className'];
        }
        return $this->arguments['fallbackClassName'];
    }
}

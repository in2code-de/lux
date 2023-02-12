<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetClassNameOnActionViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('onAction', 'string', 'Given action name', true);
        $this->registerArgument('onController', 'string', 'Given controller name', false, '');
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
            if ($this->arguments['onController'] !== '') {
                if (strtolower($this->arguments['view']['controller'])
                    === strtolower($this->arguments['onController'])) {
                    return $this->arguments['className'];
                }
            } else {
                return $this->arguments['className'];
            }
        }
        return $this->arguments['fallbackClassName'];
    }
}

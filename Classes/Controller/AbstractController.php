<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ActionController
{
    /**
     * Pass some important variables to all views
     *
     * @param ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->view->assignMultiple([
            'view' => [
                'controller' => $this->getControllerName(),
                'action' => $this->getActionName(),
                'actionUpper' => ucfirst($this->getActionName())
            ]
        ]);
    }

    /**
     * Set a filter for the last 12 month
     *
     * @return void
     * @throws InvalidArgumentNameException
     * @throws Exception
     */
    protected function setFilter(): void
    {
        $this->request->setArgument('filter', ObjectUtility::getFilterDto());
    }

    /**
     * Always set a default FilterDto even if there are no filter params. In addition remove categoryScoring with 0 to
     * avoid propertymapping exceptions
     *
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    protected function setFilterExtended(): void
    {
        $filterArgument = $this->arguments->getArgument('filter');
        $filterPropMapping = $filterArgument->getPropertyMappingConfiguration();
        $filterPropMapping->allowAllProperties();

        if ($this->request->hasArgument('filter') === false) {
            $filter = BackendUtility::getSessionValue('filter', $this->getActionName(), $this->getControllerName());
        } else {
            $filter = (array)$this->request->getArgument('filter');
            BackendUtility::saveValueToSession('filter', $this->getActionName(), $this->getControllerName(), $filter);
        }

        if (array_key_exists('categoryScoring', $filter)
            && (is_array($filter['categoryScoring']) || $filter['categoryScoring'] === '')) {
            $filter['categoryScoring'] = 0;
        }
        $this->request->setArgument('filter', $filter);
    }

    /**
     * @param string $redirectAction
     * @return void
     * @throws StopActionException
     */
    public function resetFilterAction(string $redirectAction): void
    {
        BackendUtility::saveValueToSession('filter', $redirectAction, $this->getControllerName(), []);
        $this->redirect($redirectAction);
    }

    /**
     * @return string like "Analysis" or "Lead"
     */
    protected function getControllerName(): string
    {
        $name = end(explode('\\', get_called_class()));
        return StringUtility::removeStringPostfix($name, 'Controller');
    }

    /**
     * @return string like "List" or "Detail" (uppercase)
     */
    protected function getActionName(): string
    {
        return StringUtility::removeStringPostfix($this->actionMethodName, 'Action');
    }
}

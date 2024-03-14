<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Backend;

use In2code\Lux\Domain\Service\Uri\NewRecord;
use In2code\Lux\Utility\BackendUtility;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UriNewViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('tableName', 'string', 'tableName', true);
        $this->registerArgument('moduleName', 'string', 'module name for return url', true);
        $this->registerArgument('addReturnUrl', 'bool', 'addReturnUrl', false, true);
    }

    /**
     * @return string
     * @throws RouteNotFoundException
     */
    public function render(): string
    {
        $newRecord = GeneralUtility::makeInstance(NewRecord::class, $this->renderingContext);
        return $newRecord->get(
            $this->arguments['tableName'],
            $this->getPageIdentifierForTable($this->arguments['tableName']),
            (bool)$this->arguments['addReturnUrl']
        );
    }

    /**
     * Get page identifier from user tsconfig
     *
     *  Example configuration in be_groups could be:
     *      tx_lux {
     *          defaultPage {
     *              tx_lux_domain_model_linklistener = 1
     *              tx_luxenterprise_domain_model_shortener = 2
     *              tx_luxenterprise_domain_model_utmgenerator_uri = 3
     *          }
     *      }
     *
     * @param string $tableName
     * @return int
     */
    protected function getPageIdentifierForTable(string $tableName): int
    {
        $configuration = BackendUtility::getUserTsConfigByPath('tx_lux./defaultPage.');
        if (is_array($configuration) && array_key_exists($tableName, $configuration)) {
            return (int)$configuration[$tableName];
        }
        return 0;
    }
}

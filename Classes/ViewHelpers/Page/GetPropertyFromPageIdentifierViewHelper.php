<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Page;

use In2code\Lux\Utility\DatabaseUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetPropertyFromPageIdentifierViewHelper
 */
class GetPropertyFromPageIdentifierViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('pageIdentifier', 'int', 'page identifier', true);
        $this->registerArgument('property', 'string', 'a field name of table pages', false, 'title');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $property = $this->arguments['property'];
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('pages');
        $value = $queryBuilder
            ->select($property)
            ->from('pages')
            ->where('uid=' . (int)$this->arguments['pageIdentifier'])
            ->execute()
            ->fetchColumn(0);
        if ($value === false) {
            $value = $this->arguments['pageIdentifier'];
        }
        return $value;
    }
}

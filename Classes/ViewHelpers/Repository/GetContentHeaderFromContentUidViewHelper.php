<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Repository;

use In2code\Lux\Utility\DatabaseUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetContentHeaderFromContentUidViewHelper
 * @noinspection PhpUnused
 */
class GetContentHeaderFromContentUidViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'tt_content.uid', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('tt_content');
        return (string)$queryBuilder
            ->select('header')
            ->from('tt_content')
            ->where('uid=' . (int)$this->arguments['uid'])
            ->execute()
            ->fetchColumn(0);
    }
}

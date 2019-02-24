<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Repository;

use In2code\Lux\Utility\DatabaseUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetContentHeaderFromContentUidViewHelper
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
        /** @var  $queryBuilder */
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('tt_content');
        $header = $queryBuilder
            ->select('header')
            ->from('tt_content')
            ->where('uid=' . (int)$this->arguments['uid'])
            ->execute()
            ->fetchColumn(0);
        if ($header !== false) {
            return $header;
        }
        return '';
    }
}

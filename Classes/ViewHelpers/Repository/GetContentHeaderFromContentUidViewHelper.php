<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetContentHeaderFromContentUidViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'tt_content.uid', true);
    }

    /**
     * @return string
     * @throws ExceptionDbal
     */
    public function render(): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('tt_content');
        return (string)$queryBuilder
            ->select('header')
            ->from('tt_content')
            ->where('uid=' . (int)$this->arguments['uid'])
            ->executeQuery()
            ->fetchOne();
    }
}

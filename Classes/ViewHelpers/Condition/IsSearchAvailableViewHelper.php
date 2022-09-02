<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Condition;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\SearchRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IsSearchAvailableViewHelper
 * @noinspection PhpUnused
 */
class IsSearchAvailableViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param null $arguments
     * @return bool
     * @throws ExceptionDbal
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        unset($arguments);
        $searchRepository = GeneralUtility::makeInstance(SearchRepository::class);
        return $searchRepository->isSearchTableFilled();
    }
}

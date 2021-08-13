<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Condition;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\SearchRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * @throws Exception|ExceptionDbal
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        unset($arguments);
        /** @var SearchRepository $searchRepository */
        $searchRepository = ObjectUtility::getObjectManager()->get(SearchRepository::class);
        return $searchRepository->isSearchTableFilled();
    }
}

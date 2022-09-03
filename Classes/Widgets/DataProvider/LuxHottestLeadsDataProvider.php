<?php

declare(strict_types=1);
namespace In2code\Lux\Widgets\DataProvider;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface;

/**
 * Class LuxHottestLeadsDataProvider
 * @noinspection PhpUnused
 */
class LuxHottestLeadsDataProvider implements ListDataProviderInterface
{
    /**
     * @return array
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public function getItems(): array
    {
        $list = [];
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISMONTH);
        $visitors = $visitorRepository->findByHottestScorings($filter);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $list[] = $visitor->getFullNameWithEmail() . ' - Scoring ' . $visitor->getScoring();
        }
        return $list;
    }
}

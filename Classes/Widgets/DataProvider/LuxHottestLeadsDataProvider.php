<?php
declare(strict_types = 1);
namespace In2code\Lux\Widgets\DataProvider;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class LuxHottestLeadsDataProvider
 * @noinspection PhpUnused
 */
class LuxHottestLeadsDataProvider implements ListDataProviderInterface
{
    /**
     * @return array
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function getItems(): array
    {
        $list = [];
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISMONTH);
        $visitors = $visitorRepository->findByHottestScorings($filter);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $list[] = $visitor->getFullName() . ' (' . $visitor->getScoring() . ')';
        }
        return $list;
    }
}

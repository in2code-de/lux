<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider\PageOverview;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Service\Referrer\Readable;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\FrontendUtility;
use PDO;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * GotinExternalDataProvider
 * to show 5 gotin visits from external sources to a given page identifier
 */
class GotinExternalDataProvider extends AbstractDataProvider
{
    /**
     * @var FilterDto|null
     */
    protected $filter = null;

    /**
     * @var PagevisitRepository|null
     */
    protected $pagevisitRepository = null;

    /**
     * Constructor
     *
     * @param FilterDto|null $filter contains timeframe and page identifier as searchterm
     */
    public function __construct(FilterDto $filter = null)
    {
        $this->filter = $filter;
        $this->pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
    }

    /**
     *  [
     *      'Twitter' => 123,
     *      'Facebook' => 12
     *  ]
     * @return array
     * @throws ExceptionDbal
     */
    public function get(): array
    {
        $result = [];
        foreach ($this->getExternalGotinToPagevisit() as $referrer) {
            $readable = GeneralUtility::makeInstance(Readable::class, $referrer['referrer']);
            $domainName = $readable->getReadableReferrer();
            if (array_key_exists($domainName, $result)) {
                $result[$domainName]++;
            } else {
                $result[$domainName] = 1;
            }
        }
        arsort($result);
        $result = array_slice($result, 0, $this->limit);
        return $result;
    }

    /**
     * @return array
     * @throws ExceptionDbal
     */
    protected function getExternalGotinToPagevisit(): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $referrers = $connection->executeQuery(
            'select referrer from ' . Pagevisit::TABLE_NAME
            . ' where referrer != ""'
            . ' and referrer not like "%' . $this->getDomainOfPageIdentifier((int)$this->filter->getSearchterm()) . '%"'
            . ' and page=' . (int)$this->filter->getSearchterm()
            . ' and crdate > ' . $this->filter->getStartTimeForFilter()->format('U')
            . ' and crdate < ' . $this->filter->getEndTimeForFilter()->format('U')
        )->fetchAll(PDO::FETCH_ASSOC);
        if ($referrers === false) {
            return [];
        }
        return $referrers;
    }

    /**
     * Always try to get a domain from a given page identifier. If no domain could be build (e.g. if no domain is set
     * in site configuration but only a slash, use the current domain as fallback)
     *
     * @param int $pageIdentifier
     * @return string
     */
    protected function getDomainOfPageIdentifier(int $pageIdentifier): string
    {
        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($pageIdentifier);
            $domain = (string)$site->getBase();
            $domain = preg_replace('~(https?://)~i', '', $domain);
            $domain = rtrim($domain, '/');
            if ($domain !== '') {
                return $domain;
            }
        } catch (\Exception $exception) {
            // fallback below
        }
        return FrontendUtility::getCurrentDomain();
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class DomainNewsDataProvider
 */
class DomainNewsDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          123,
     *          50,
     *          12
     *      ],
     *      'titles' => [
     *          'Page visits domain1.org',
     *          'Page visits domain2.org',
     *          'Page visits domain3.org'
     *      ]
     *  ]
     *
     * @return void
     * @throws Exception
     */
    public function prepareData(): void
    {
        $languages = $this->getDomains();
        foreach ($languages as $language) {
            $this->data['amounts'][] = $language['count'];
            $this->data['titles'][] = $language['label'];
        }
    }

    /**
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    protected function getDomains(): array
    {
        /** @var NewsvisitRepository $newsvisitRepository */
        $newsvisitRepository = ObjectUtility::getObjectManager()->get(NewsvisitRepository::class);
        $rows = $newsvisitRepository->getDomainsWithAmountOfVisits($this->filter);

        foreach ($rows as &$row) {
            $row['label'] = LocalizationUtility::translateByKey('dataprovider.domain.label', [$row['domain']]);
        }
        return $rows;
    }
}

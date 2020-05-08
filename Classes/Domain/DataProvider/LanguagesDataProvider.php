<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\LocalizationUtility;

/**
 * Class LanguagesDataProvider
 */
class LanguagesDataProvider extends AbstractDataProvider
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
     *          'Page visits "German"',
     *          'Page visits "English"',
     *          'Page visits "Japanese"'
     *      ]
     *  ]
     *
     * @return void
     * @throws DBALException
     */
    public function prepareData(): void
    {
        $languages = $this->getLanguagesFromSystem();
        foreach ($languages as $language) {
            $this->data['amounts'][] = $language['count'];
            $this->data['titles'][] = $language['label'];
        }
    }

    /**
     * @return array
     * @throws DBALException
     */
    protected function getLanguagesFromSystem(): array
    {
        $connection = DatabaseUtility::getConnectionForTable('sys_language');
        $sql = 'SELECT count(*) as count, pv.language, l.title FROM ' . Pagevisit::TABLE_NAME . ' pv'
            . ' left join sys_language l on l.uid = pv.language group by pv.language order by count desc ';
        $rows = (array)$connection->executeQuery($sql)->fetchAll();

        foreach ($rows as &$row) {
            $row['label'] = $row['title'] ?: 'Standard';
            $row['label'] = LocalizationUtility::translateByKey('dataprovider.languages.label', [$row['label']]);
        }
        return $rows;
    }
}

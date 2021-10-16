<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $rows = $pagevisitRepository->getAllLanguages($this->filter);

        foreach ($rows as &$row) {
            $row['label'] = $row['title'] ?: 'Standard';
            $row['label'] = LocalizationUtility::translateByKey('dataprovider.languages.label', [$row['label']]);
        }
        return $rows;
    }
}

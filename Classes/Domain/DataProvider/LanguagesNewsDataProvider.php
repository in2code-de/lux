<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Lux\Domain\Repository\LanguageRepository;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguagesNewsDataProvider extends AbstractDataProvider
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
     * @throws ExceptionDbalDriver
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
     * @throws ExceptionDbalDriver
     */
    protected function getLanguagesFromSystem(): array
    {
        $newsvisitRepository = GeneralUtility::makeInstance(NewsvisitRepository::class);
        $languageRepository = GeneralUtility::makeInstance(LanguageRepository::class);
        $rows = $newsvisitRepository->getAllLanguages($this->filter);

        foreach ($rows as &$row) {
            $row['label'] = $languageRepository->getLabelToLanguageIdentifier($row['language']);
            $row['label'] = LocalizationUtility::translateByKey('dataprovider.languages.label', [$row['label']]);
        }
        return $rows;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\UserFunc;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\EnvironmentUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class EnableStatus
{
    protected string $templatePathAndFile = 'EXT:lux/Resources/Private/Templates/UserFunc/EnableStatus.html';

    /**
     * @return string
     * @throws ExceptionDbal
     * @noinspection PhpUnused
     */
    public function showEnableStatus(): string
    {
        $variables = [
            'status' => EnvironmentUtility::isComposerMode() && ExtensionManagementUtility::isLoaded('lux'),
            'composerMode' => EnvironmentUtility::isComposerMode(),
            'enabled' => [
                'lux' => ExtensionManagementUtility::isLoaded('lux'),
                'luxenterprise' => ExtensionManagementUtility::isLoaded('luxenterprise'),
            ],
            'stats' => [
                'visitors' => count($this->getVisitors('1=1')),
                'visitorsIdentified' => count($this->getVisitors()),
                'visitorsUnidentified' => count($this->getVisitors('identified=0')),
            ],
        ];
        return $this->renderMarkup($variables);
    }

    protected function renderMarkup(array $variables): string
    {
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->templatePathAndFile));
        $standaloneView->assignMultiple($variables);
        return $standaloneView->render();
    }

    /**
     * @param string $where
     * @return array
     * @throws ExceptionDbal
     */
    protected function getVisitors(string $where = 'identified=1'): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Visitor::TABLE_NAME);
        return $queryBuilder
            ->select('uid')
            ->from(Visitor::TABLE_NAME)
            ->where($where)
            ->executeQuery()
            ->fetchAllAssociative();
    }
}

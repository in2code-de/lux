<?php

declare(strict_types=1);
namespace In2code\Lux\Update;

use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('luxMigratePluginCTypeUpgradeWizard')]
class MigratePluginCTypeUpgradeWizard implements UpgradeWizardInterface
{
    private const CTYPE_LIST = 'list';
    private const LIST_TYPE = 'lux_pi1';
    private const CTYPE_NEW = 'lux_pi1';

    public function getIdentifier(): string
    {
        return 'luxMigratePluginCTypeUpgradeWizard';
    }

    public function getTitle(): string
    {
        return 'LUX: Migrate plugin content elements from list_type to dedicated CType';
    }

    public function getDescription(): string
    {
        return 'Migrates tt_content records with CType "list" and list_type "lux_pi1" to use the'
            . ' dedicated CType "lux_pi1" as required for TYPO3 13+.';
    }

    public function executeUpdate(): bool
    {
        $connection = DatabaseUtility::getConnectionForTable('tt_content');
        $connection->update(
            'tt_content',
            ['CType' => self::CTYPE_NEW, 'list_type' => ''],
            ['CType' => self::CTYPE_LIST, 'list_type' => self::LIST_TYPE]
        );
        return true;
    }

    public function updateNecessary(): bool
    {
        $connection = DatabaseUtility::getConnectionForTable('tt_content');
        return $connection->count('uid', 'tt_content', [
            'CType' => self::CTYPE_LIST,
            'list_type' => self::LIST_TYPE,
        ]) > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}

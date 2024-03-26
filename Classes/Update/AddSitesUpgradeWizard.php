<?php

declare(strict_types=1);
namespace In2code\Lux\Update;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Update\AddSites\AddSitesForDownloads;
use In2code\Lux\Update\AddSites\AddSitesForLinkclick;
use In2code\Lux\Update\AddSites\AddSitesForPagevisits;
use In2code\Lux\Utility\DatabaseUtility;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('addSitesUpgradeWizard')]
class AddSitesUpgradeWizard implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'addSitesUpgradeWizard';
    }

    public function getTitle(): string
    {
        return 'LUX: Add sites to existing pagevisit records for multiclient functionality';
    }

    public function getDescription(): string
    {
        return 'Fill some database fields with information about sites. Attention: Because of the long runtime, it is' .
            ' recommended that this upgrade wizard will be started from CLI';
    }

    public function executeUpdate(): bool
    {
        try {
            GeneralUtility::makeInstance(AddSitesForPagevisits::class)->run();
            GeneralUtility::makeInstance(AddSitesForDownloads::class)->run();
            GeneralUtility::makeInstance(AddSitesForLinkclick::class)->run();
        } catch (Throwable $exception) {
            return false;
        }
        return true;
    }

    public function updateNecessary(): bool
    {
        return DatabaseUtility::isFieldExistingInTable('site', Pagevisit::TABLE_NAME)
            && DatabaseUtility::isAnyFieldFilledInTable('site', Pagevisit::TABLE_NAME) === false;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}

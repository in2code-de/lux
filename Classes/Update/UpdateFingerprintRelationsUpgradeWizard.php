<?php

declare(strict_types=1);
namespace In2code\Lux\Update;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('updateFingerprintRelationsUpgradeWizard')]
class UpdateFingerprintRelationsUpgradeWizard implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'updateFingerprintRelationsUpgradeWizard';
    }

    public function getTitle(): string
    {
        return 'LUX: Change relation from visitor to fingerprint table';
    }

    public function getDescription(): string
    {
        return 'Before this task, fingerprint uids were stored commaseparated in visitor table. Now we store the' .
            ' uid of the visitor in the fingerprint table for performance reasons.' .
            ' Attention: Because of the long runtime, it is recommended that this upgrade wizard will be started from' .
            ' CLI.';
    }

    public function executeUpdate(): bool
    {
        $connectionFingerprint = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        $connectionVisitor = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $visitors = $this->getAllVisitors();
        foreach ($visitors as $visitor) {
            $fingerprintIdentifiers = GeneralUtility::intExplode(',', $visitor['fingerprints'], true);
            foreach ($fingerprintIdentifiers as $fingerprintIdentifier) {
                if ($fingerprintIdentifier === 0) {
                    continue;
                }
                $connectionFingerprint->executeQuery(
                    'update ' . Fingerprint::TABLE_NAME
                    . ' set visitor = "' . $visitor['uid'] . '" where uid=' . $fingerprintIdentifier
                );
            }
            $connectionVisitor->executeQuery(
                'update ' . Visitor::TABLE_NAME . ' set fingerprints = ' . count($fingerprintIdentifiers) . ' WHERE uid = ' . $visitor['uid']
            );
        }
        return true;
    }

    protected function getAllVisitors(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Visitor::TABLE_NAME, true);
        $fingerprintsWithVisitor = $this->getFingerprintsWithVisitor();
        $query = $queryBuilder
            ->select('uid', 'fingerprints')
            ->from(Visitor::TABLE_NAME)
            ->where('deleted=0 and fingerprints!=\'\'');
        if (!empty($fingerprintsWithVisitor)) {
            $query->andWhere('uid NOT IN (' . implode(',', $fingerprintsWithVisitor) . ')');
        }
        return $query->executeQuery()->fetchAllAssociative();
    }

    protected function getFingerprintsWithVisitor(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Fingerprint::TABLE_NAME, true);
        $data = $queryBuilder
            ->select('uid')
            ->from(Fingerprint::TABLE_NAME)
            ->where('deleted=0 and visitor != 0')
            ->executeQuery()
            ->fetchAllAssociative();
        $uids = [];
        foreach ($data as $item) {
            $uids[] = $item['uid'];
        }
        return $uids;
    }

    public function updateNecessary(): bool
    {
        return DatabaseUtility::isFieldExistingInTable('visitor', Fingerprint::TABLE_NAME)
            && DatabaseUtility::isAnyFieldFilledInTable('visitor', Fingerprint::TABLE_NAME) === false;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}

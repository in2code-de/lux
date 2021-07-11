<?php
declare(strict_types = 1);
namespace In2code\Lux\Update;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Class MoveReferrerToPageVisitUpgradeWizard
 * to copy values of tx_lux_domain_model_visitor.referrer to the oldest related tx_lux_domain_model_pagevisit.referrer
 */
class MoveReferrerToPageVisitUpgradeWizard implements UpgradeWizardInterface
{
    const TABLE_NAME_OLD = 'tx_lux_domain_model_visitor';
    const TABLE_NAME_NEW = 'tx_lux_domain_model_pagevisit';

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'moveReferrerToPageVisitUpgradeWizard';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Lux: Copy values from visitor.referrer to pagevisit.referrer';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Basicly move values values of tx_lux_domain_model_visitor.referrer to the oldest related ' .
            'tx_lux_domain_model_pagevisit.referrer';
    }

    /**
     * @return bool
     */
    public function executeUpdate(): bool
    {
        try {
            $this->copyReferrers();
            $this->emptyOldField();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * @return void
     */
    protected function copyReferrers(): void
    {
        foreach ($this->getReferrers() as $data) {
            $pagevisitIdentifier = $this->getFirstPagevisitIdentifierFromVisitor($data['uid']);
            $this->setReferrerInPagevisit($pagevisitIdentifier, $data['referrer']);
        }
    }

    /**
     * @param int $visitor
     * @return int
     */
    protected function getFirstPagevisitIdentifierFromVisitor(int $visitor): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME_NEW, true);
        return (int)$queryBuilder
            ->select('uid')
            ->from(self::TABLE_NAME_NEW)
            ->where('visitor=' . (int)$visitor)
            ->orderBy('crdate', 'asc')
            ->setMaxResults(1)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param int $pagevisitIdentifier
     * @param string $referrer
     * @return void
     */
    protected function setReferrerInPagevisit(int $pagevisitIdentifier, string $referrer): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME_NEW, true);
        $queryBuilder
            ->update(self::TABLE_NAME_NEW)
            ->where('uid=' . (int)$pagevisitIdentifier)
            ->set('referrer', $referrer)
            ->execute();
    }

    /**
     * @return array
     */
    protected function getReferrers(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME_OLD, true);
        return (array)$queryBuilder
            ->select('uid', 'referrer')
            ->from(self::TABLE_NAME_OLD)
            ->where('referrer != ""')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return void
     * @throws DBALException
     */
    protected function emptyOldField(): void
    {
        $connection = DatabaseUtility::getConnectionForTable(self::TABLE_NAME_OLD);
        $connection->executeQuery('update ' . self::TABLE_NAME_OLD . ' set referrer=""');
    }

    /**
     * @return bool
     * @throws DBALException
     */
    public function updateNecessary(): bool
    {
        return DatabaseUtility::isFieldExistingInTable('referrer', self::TABLE_NAME_OLD)
            && DatabaseUtility::isFieldInTableFilled('referrer', self::TABLE_NAME_OLD);
    }

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}

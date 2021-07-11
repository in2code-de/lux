<?php
declare(strict_types = 1);
namespace In2code\Lux\Update;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Class LuxIdCookieToFingerprintUpgradeWizard
 * to copy values of tx_lux_domain_model_idcookie to tx_lux_domain_model_fingerprint
 * and set tx_lux_domain_model_fingerprint.type=1 for those values
 */
class LuxIdCookieToFingerprintUpgradeWizard implements UpgradeWizardInterface
{
    const TABLE_NAME_OLD = 'tx_lux_domain_model_idcookie';

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'luxIdCookieToFingerprintUpgradeWizard';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Lux: Copy values from idcookie table to fingerprint table';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Basicly copy values of tx_lux_domain_model_idcookie to tx_lux_domain_model_fingerprint'
            . ' and set tx_lux_domain_model_fingerprint.type=1 for those values';
    }

    /**
     * @return bool
     */
    public function executeUpdate(): bool
    {
        try {
            $this->cloneTable();
            $this->cloneRelations();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * @return void
     */
    protected function cloneTable(): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME_OLD);
        $records = (array)$queryBuilder
            ->select('uid', 'pid', 'value', 'domain', 'user_agent', 'tstamp', 'crdate')
            ->from(self::TABLE_NAME_OLD)
            ->execute()
            ->fetchAll();
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        foreach ($records as $record) {
            $properties = $record + ['type' => 1];
            $connection->insert(Fingerprint::TABLE_NAME, $properties);
        }
    }

    /**
     * @return void
     * @throws DBALException
     */
    protected function cloneRelations(): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection->executeQuery('update ' . Visitor::TABLE_NAME . ' set fingerprints=idcookies;');
    }

    /**
     * @return bool
     * @throws DBALException
     */
    public function updateNecessary(): bool
    {
        return $this->isIdCookieTableExisting() && $this->isIdCookieTableFilled()
            && $this->isFingerprintTableFilled() === false;
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

    /**
     * @return bool
     * @throws DBALException
     */
    protected function isIdCookieTableExisting(): bool
    {
        return DatabaseUtility::isTableExisting(self::TABLE_NAME_OLD);
    }

    /**
     * @return bool
     */
    protected function isIdCookieTableFilled(): bool
    {
        return DatabaseUtility::isTableFilled(self::TABLE_NAME_OLD);
    }

    /**
     * @return bool
     */
    protected function isFingerprintTableFilled(): bool
    {
        return DatabaseUtility::isTableFilled(Fingerprint::TABLE_NAME);
    }
}

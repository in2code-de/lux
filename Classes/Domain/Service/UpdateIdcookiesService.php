<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Idcookie;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UpdateIdcookiesService to create tx_lux_domain_model_idcookie records out of old entries in
 * tx_lux_domain_model_visitor.id_cookie from lux 1.x or 2.x
 */
class UpdateIdcookiesService
{
    /**
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * ext_update constructor.
     */
    public function __construct()
    {
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
    }

    /**
     * @return bool
     * @throws DBALException
     */
    public function isUpdateAvailable(): bool
    {
        return DatabaseUtility::isFieldExistingInTable('id_cookie', Visitor::TABLE_NAME)
            && $this->visitorRepository->findOneVisitorWithOutdatedCookieId() !== '';
    }

    /**
     * @param string $domain
     * @return void
     * @throws DBALException
     */
    public function createNewRecordsFromOldRecords(string $domain = '')
    {
        if ($this->isUpdateAvailable()) {
            $visitors = $this->visitorRepository->findVisitorsWithOutdatedCookieId();
            foreach ($visitors as $visitor) {
                $uidCookie = $this->createNewCookieRecord($visitor, $domain);
                $this->updateVisitorRecord($visitor['uid'], $uidCookie);
            }
        } else {
            throw new \LogicException('Update is not possible. Maybe update was already done?', 1559946241);
        }
    }

    /**
     * @param array $visitor
     * @param string $domain
     * @return int
     */
    protected function createNewCookieRecord(array $visitor, string $domain): int
    {
        $properties = [
            'crdate' => time(),
            'tstamp' => time(),
            'value' => $visitor['id_cookie'],
            'domain' => !empty($domain) ? $domain : GeneralUtility::getIndpEnv('HTTP_HOST'),
            'user_agent' => $visitor['user_agent']
        ];
        $connection = DatabaseUtility::getConnectionForTable(Idcookie::TABLE_NAME);
        $connection->insert(Idcookie::TABLE_NAME, $properties);
        return (int)$connection->lastInsertId(Idcookie::TABLE_NAME);
    }

    /**
     * @param int $visitor
     * @param int $uidCookie
     * @return void
     */
    protected function updateVisitorRecord(int $visitor, int $uidCookie)
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Visitor::TABLE_NAME);
        $queryBuilder
            ->update(Visitor::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('uid', $visitor))
            ->set('id_cookie', '')
            ->set('idcookies', $uidCookie)
            ->execute();
    }
}

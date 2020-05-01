<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Exception\ClassDoesNotExistException;
use In2code\Lux\Utility\DatabaseUtility;

/**
 * Class FingerprintRepository
 */
class FingerprintRepository extends AbstractRepository
{

    /**
     * Get an array with sorted values with a limit of 1000:
     * [
     *      'twitter.com' => 234,
     *      'facebook.com' => 123
     * ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws ClassDoesNotExistException
     */
    public function getAmountOfUserAgents(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        $sql = 'select user_agent, count(user_agent) count from ' . Fingerprint::TABLE_NAME
            . ' where user_agent != "" and crdate > ' . $filter->getStartTimeForFilter()->format('U')
            . ' and crdate <' . $filter->getEndTimeForFilter()->format('U')
            . ' group by user_agent having (count > 1) order by count desc limit 1000';
        $records = (array)$connection->executeQuery($sql)->fetchAll();
        $result = [];
        foreach ($records as $record) {
            if (class_exists(\WhichBrowser\Parser::class)) {
                $parser = new \WhichBrowser\Parser($record['user_agent']);
                $osBrowser = $parser->os->name . ' ' . $parser->browser->name;
                if (array_key_exists($osBrowser, $result)) {
                    $result[$osBrowser] += $record['count'];
                } else {
                    $result[$osBrowser] = $record['count'];
                }
            } else {
                throw new ClassDoesNotExistException('\WhichBrowser\Parser class is missing', 1588337756);
            }
        }
        arsort($result);
        return $result;
    }
}

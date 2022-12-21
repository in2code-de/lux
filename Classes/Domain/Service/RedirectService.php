<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\ParametersException;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;

/**
 * Class RedirectService
 * allows to quickly store and read redirects to combine Server- and Clientside actions with redirects
 */
class RedirectService
{
    const TABLE_NAME = 'tx_lux_redirect';

    /**
     * @param string $target e.g. "t3://page?uid=123" or "https://domain.org" or "/path/page.html"
     * @param array $arguments
     * @return string e.g. "abc123"
     */
    public function addTarget(string $target, array $arguments = []): string
    {
        $hash = StringUtility::getRandomString(56, false);
        $this->persistRedirect($target, $hash, $arguments);
        return $hash;
    }

    /**
     * @param string $hash
     * @return string
     * @throws ParametersException
     * @throws ConfigurationException
     */
    public function getParsedTargetByHash(string $hash): string
    {
        if (empty($hash)) {
            throw new ParametersException('Hash is empty', 1593073222);
        }
        $redirect = $this->findByHash($hash);
        $target = $redirect['target'];
        $parsedTarget = ObjectUtility::getContentObject()->typoLink_URL(['parameter' => $target]);
        if (empty($parsedTarget)) {
            throw new ConfigurationException('Target could not be parsed', 1593073343);
        }
        return $parsedTarget;
    }

    /**
     * @param string $hash
     * @return array
     * @throws ParametersException
     */
    public function getArgumentsByHash(string $hash): array
    {
        if (empty($hash)) {
            throw new ParametersException('Hash is empty', 1593073222);
        }
        $redirect = $this->findByHash($hash);
        return json_decode($redirect['arguments'], true);
    }

    /**
     * @param string $hash
     * @return array
     * @throws ParametersException
     * @throws Exception
     * @throws ExceptionDbal
     */
    protected function findByHash(string $hash): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable($this::TABLE_NAME);
        $result = $queryBuilder
            ->select('*')
            ->from($this::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('hash', $queryBuilder->createNamedParameter($hash)),
                $queryBuilder->expr()->eq('deleted', 0)
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        if ($result === false) {
            throw new ParametersException('Redirect not found', 1593073397);
        }
        return $result;
    }

    /**
     * @param string $target
     * @param string $hash
     * @param array $arguments
     * @return void
     */
    protected function persistRedirect(string $target, string $hash, array $arguments = []): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable($this::TABLE_NAME);
        $queryBuilder
            ->insert($this::TABLE_NAME)
            ->values([
                'target' => $target,
                'hash' => $hash,
                'arguments' => json_encode($arguments),
                'tstamp' => time(),
                'crdate' => time(),
            ])
            ->executeQuery();
    }
}

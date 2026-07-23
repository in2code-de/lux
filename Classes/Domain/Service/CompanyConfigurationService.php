<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\Remote\LeadfeederRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Database\Connection;

/**
 * Class CompanyConfigurationService
 * to save leadfeeder credentials (api key and account id) as TypoScript constants from the companies backend view
 */
class CompanyConfigurationService
{
    private const TOKEN_LENGTH = 40;
    private const TOKEN_PATTERN = '~[0-9a-f]{' . self::TOKEN_LENGTH . '}~';
    public const TEMPLATE_TABLE = 'sys_template';

    protected array $configuration = [
        'plugin.tx_lux.settings.tracking.company.enable = 1',
        'plugin.tx_lux.settings.tracking.company.token = %1$s',
        'plugin.tx_lux.settings.tracking.company.accountId = %2$s',
        'plugin.tx_lux.settings.tracking.company.connectionLimit = 5000',
        'plugin.tx_lux.settings.tracking.company.autoConvert.enable = 1',
    ];

    protected LeadfeederRepository $leadfeederRepository;

    public function __construct(LeadfeederRepository $leadfeederRepository)
    {
        $this->leadfeederRepository = $leadfeederRepository;
    }

    /**
     * @param string $token
     * @param string $accountId
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    public function add(string $token, string $accountId): void
    {
        $this->isValidConfiguration($token, $accountId);
        foreach ($this->getRootTemplates() as $template) {
            $constants = $template['constants'] . PHP_EOL . PHP_EOL
                . implode(PHP_EOL, $this->getConfiguration($token, $accountId));
            $this->updateTemplate($template['uid'], $constants);
        }
    }

    protected function updateTemplate(int $identifier, string $constants): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TEMPLATE_TABLE);
        $queryBuilder
            ->update(self::TEMPLATE_TABLE)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($identifier, Connection::PARAM_INT)
                )
            )
            ->set('constants', $constants)
            ->executeStatement();
    }

    /**
     * @return array
     * @throws ExceptionDbal
     */
    protected function getRootTemplates(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TEMPLATE_TABLE);
        return (array)$queryBuilder
            ->select('*')
            ->from(self::TEMPLATE_TABLE)
            ->where(
                $queryBuilder->expr()->eq('root', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param string $token
     * @param string $accountId
     * @return void
     * @throws ConfigurationException
     */
    protected function isValidConfiguration(string $token, string $accountId): void
    {
        $this->isCorrectSpelling($token);
        $this->isCorrectAccountId($accountId);
        $this->isAcceptedByInterface($token, $accountId);
    }

    /**
     * @param string $token
     * @return void
     * @throws ConfigurationException
     */
    protected function isCorrectSpelling(string $token): void
    {
        preg_match(self::TOKEN_PATTERN, $token, $result);
        if (strlen($token) !== self::TOKEN_LENGTH || ($result[0] ?? '') !== $token) {
            throw new ConfigurationException(
                LocalizationUtility::translateByKey('module.companiesDisabled.token.failureSpelling'),
                1687114799
            );
        }
    }

    /**
     * @param string $accountId
     * @return void
     * @throws ConfigurationException
     */
    protected function isCorrectAccountId(string $accountId): void
    {
        if (ctype_digit($accountId) === false) {
            throw new ConfigurationException(
                LocalizationUtility::translateByKey('module.companiesDisabled.token.failureAccountId'),
                1687183621
            );
        }
    }

    /**
     * @param string $token
     * @param string $accountId
     * @return void
     * @throws ConfigurationException
     */
    protected function isAcceptedByInterface(string $token, string $accountId): void
    {
        if ($this->leadfeederRepository->validateCredentials($token, $accountId) === false) {
            throw new ConfigurationException(
                LocalizationUtility::translateByKey('module.companiesDisabled.token.failureInterface'),
                1687183620
            );
        }
    }

    protected function getConfiguration(string $token, string $accountId): array
    {
        $configuration = $this->configuration;
        foreach ($configuration as &$line) {
            $line = sprintf($line, $token, $accountId);
        }
        return $configuration;
    }
}

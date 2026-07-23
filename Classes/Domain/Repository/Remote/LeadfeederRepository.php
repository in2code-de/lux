<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository\Remote;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Domain\Service\LogService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\IpUtility;
use In2code\Lux\Utility\ObjectUtility;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * Class LeadfeederRepository to enrich a visitor with company information from leadfeeder.com via IP address.
 *
 * Authentication uses the X-Api-Key header together with an account_id. Both credentials are read from the
 * TypoScript settings (tracking.company.token and tracking.company.accountId) with a fallback to the
 * environment variables LUX_LEADFEEDER_TOKEN and LUX_LEADFEEDER_ACCOUNT_ID (e.g. set in .env.local).
 */
class LeadfeederRepository
{
    private const INTERFACE_URL = 'https://api.leadfeeder.com/v1/ip/enrich';
    private const LIMIT_MONTH_ABSOLUTE = 100000;
    private const ENV_TOKEN = 'LUX_LEADFEEDER_TOKEN';
    private const ENV_ACCOUNT_ID = 'LUX_LEADFEEDER_ACCOUNT_ID';
    private const VALIDATION_IP = '8.8.8.8';

    /**
     * Upper bound (exclusive) of yearly revenue in Euro per revenue_class code (see dictionary.revenue_class in
     * locallang_db.xlf). The last class (09) is used for everything above the highest bound.
     */
    private const REVENUE_CLASSES = [
        '01' => 100000,
        '02' => 250000,
        '03' => 500000,
        '04' => 2500000,
        '05' => 5000000,
        '06' => 25000000,
        '07' => 50000000,
        '08' => 500000000,
    ];

    /**
     * Upper bound (inclusive) of the employee count per size_class code (see dictionary.size_class in
     * locallang_db.xlf). The last class (10) is used for everything above the highest bound.
     */
    private const SIZE_CLASSES = [
        '01' => 4,
        '02' => 9,
        '03' => 19,
        '04' => 49,
        '05' => 99,
        '06' => 199,
        '07' => 499,
        '08' => 999,
        '09' => 1999,
    ];

    protected VisitorRepository $visitorRepository;
    protected LogRepository $logRepository;
    protected RequestFactory $requestFactory;
    protected LogService $logService;

    protected array $settings = [];

    public function __construct(
        VisitorRepository $visitorRepository,
        LogRepository $logRepository,
        RequestFactory $requestFactory,
        LogService $logService
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->logRepository = $logRepository;
        $this->requestFactory = $requestFactory;
        $this->logService = $logService;
        $configurationService = ObjectUtility::getConfigurationService();
        $this->settings = $configurationService->getTypoScriptSettings();
    }

    /**
     * @param Visitor $visitor
     * @param string $ipAddress use current IP address when empty
     * @return array normalized company properties or an empty array when there was no company hit
     * @throws ConfigurationException
     */
    public function getPropertiesForIpAddress(Visitor $visitor, string $ipAddress = ''): array
    {
        $properties = [];
        if ($this->isConnectionLimitReached() === false) {
            try {
                $this->logService->logCompanyEnrichConnection($visitor);
                $response = $this->requestFactory->request($this->getUriForIpAddress($ipAddress), 'GET', $this->getRequestOptions());
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody()->getContents(), true);
                    if ($this->isCompanyHit($data)) {
                        $this->logService->logCompanyEnrichConnectionSuccess($visitor);
                        $properties = $this->normalizeProperties($data);
                    }
                }
            } catch (Throwable) {
            }
        }
        return $properties;
    }

    /**
     * Statistics for the backend module views. Because leadfeeder does not offer a quota endpoint, the values are
     * derived from the internal request log. Returns an empty array when the interface is not configured.
     */
    public function getStatus(): array
    {
        $hits = $this->logRepository->findAmountOfSuccessfulCompanyEnrichLogsOfCurrentMonth();
        $connections = $this->logRepository->findAmountOfCompanyEnrichLogsOfCurrentMonth();
        $now = new \DateTime();
        return [
            [
                'year' => (int)$now->format('Y'),
                'month' => (int)$now->format('n'),
                'hits' => $hits,
                'misses' => max(0, $connections - $hits),
            ],
        ];
    }

    public function validateCredentials(string $token, string $accountId): bool
    {
        $valid = false;
        try {
            $uri = self::INTERFACE_URL . '?ip=' . rawurlencode(self::VALIDATION_IP) . '&account_id=' . rawurlencode($accountId);
            $response = $this->requestFactory->request($uri, 'GET', $this->getRequestOptions($token));
            $valid = in_array($response->getStatusCode(), [401, 403], true) === false;
        } catch (Throwable $exception) {
        }
        return $valid;
    }

    public function isConfigured(): bool
    {
        return $this->getToken() !== '' && $this->getAccountId() !== '';
    }

    protected function isCompanyHit(mixed $data): bool
    {
        return is_array($data)
            && ($data['data']['attributes']['company_type'] ?? '') === 'company'
            && isset($data['data']['relationships']['company']['attributes']);
    }

    protected function normalizeProperties(array $data): array
    {
        $company = $data['data']['relationships']['company']['attributes'];
        $address = $company['address'] ?? [];
        return [
            'name' => (string)($company['name'] ?? ''),
            'domain' => $this->normalizeDomain((string)($company['url'] ?? '')),
            'branch_code' => (string)($company['industries']['wz'][0]['code'] ?? ''),
            'city' => (string)($address['city'] ?? ''),
            'zip' => (string)($address['postal_code'] ?? ''),
            'street' => (string)($address['street_address'] ?? ''),
            'region' => (string)($address['region'] ?? ''),
            'country_code' => strtolower((string)($address['country_code'] ?? '')),
            'continent' => '',
            'founding_year' => (string)($company['founded_year'] ?? ''),
            'phone' => (string)($company['phones'][0]['number'] ?? ''),
            'revenue' => (string)($company['revenue']['value_eur'] ?? $company['revenue']['value'] ?? ''),
            'revenue_class' => $this->deriveRevenueClass($company['revenue'] ?? []),
            'size' => (string)($company['employee_count'] ?? ''),
            'size_class' => $this->deriveSizeClass($company['employee_count'] ?? null),
            'description' => (string)($company['description'] ?? ''),
        ];
    }

    protected function normalizeDomain(string $url): string
    {
        return preg_replace('~^https?://~', '', $url);
    }

    protected function deriveRevenueClass(array $revenue): string
    {
        $class = '';
        $value = (int)($revenue['value_eur'] ?? $revenue['value'] ?? 0);
        if ($value > 0) {
            $class = '09';
            foreach (self::REVENUE_CLASSES as $code => $upperBound) {
                if ($value < $upperBound) {
                    $class = $code;
                    break;
                }
            }
        }
        return $class;
    }

    protected function deriveSizeClass(mixed $employeeCount): string
    {
        $class = '';
        $count = (int)($employeeCount ?? 0);
        if ($count > 0) {
            $class = '10';
            foreach (self::SIZE_CLASSES as $code => $upperBound) {
                if ($count <= $upperBound) {
                    $class = $code;
                    break;
                }
            }
        }
        return $class;
    }

    protected function getRequestOptions(string $token = ''): array
    {
        if ($token === '') {
            $token = $this->getToken();
        }
        return [
            'headers' => ['X-Api-Key' => $token],
            'http_errors' => false,
        ];
    }

    /**
     * @param string $ipAddress
     * @return string
     * @throws ConfigurationException
     */
    protected function getUriForIpAddress(string $ipAddress): string
    {
        if ($ipAddress === '') {
            $ipAddress = IpUtility::getIpAddress();
        }
        if ($this->isConfigured() === false) {
            throw new ConfigurationException('No leadfeeder credentials defined in TypoScript or environment', 1684433462);
        }
        return self::INTERFACE_URL
            . '?ip=' . rawurlencode($ipAddress)
            . '&account_id=' . rawurlencode($this->getAccountId());
    }

    protected function getToken(): string
    {
        $token = trim($this->settings['tracking']['company']['token'] ?? '');
        if ($token === '') {
            $token = trim((string)(getenv(self::ENV_TOKEN) ?: ''));
        }
        return $token;
    }

    protected function getAccountId(): string
    {
        $accountId = trim($this->settings['tracking']['company']['accountId'] ?? '');
        if ($accountId === '') {
            $accountId = trim((string)(getenv(self::ENV_ACCOUNT_ID) ?: ''));
        }
        return $accountId;
    }

    protected function isConnectionLimitReached(): bool
    {
        $logsOfCurrentMonth = $this->logRepository->findAmountOfCompanyEnrichLogsOfCurrentMonth();
        return $this->isAbsoluteConnectionLimitReached($logsOfCurrentMonth)
            || $this->isConnectionLimitPerMonthReached($logsOfCurrentMonth)
            || $this->isConnectionLimitPerHourReached();
    }

    protected function isAbsoluteConnectionLimitReached(int $logsOfCurrentMonth): bool
    {
        return $logsOfCurrentMonth >= self::LIMIT_MONTH_ABSOLUTE;
    }

    protected function isConnectionLimitPerMonthReached(int $logsOfCurrentMonth): bool
    {
        return $logsOfCurrentMonth >= (int)($this->settings['tracking']['company']['connectionLimit'] ?? 0);
    }

    protected function isConnectionLimitPerHourReached(): bool
    {
        $logsOfCurrentHour = $this->logRepository->findAmountOfCompanyEnrichLogsOfCurrentHour();
        return $logsOfCurrentHour >= (int)($this->settings['tracking']['company']['connectionLimitPerHour'] ?? 0);
    }
}

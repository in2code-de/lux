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

class WiredmindsRepository
{
    private const INTERFACE_URL = 'https://ip2c.wiredminds.com/';
    private const LIMIT_MONTH_ABSOLUTE = 1000000;

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

    public function getPropertiesForIpAddress(Visitor $visitor, string $ipAddress = ''): array
    {
        if ($this->isConnectionLimitReached()) {
            return [];
        }

        try {
            $this->logService->logWiredmindsConnection($visitor);
            $result = $this->requestFactory->request($this->getUriForIpAddress($ipAddress));
            if ($result->getStatusCode() === 200) {
                $properties = json_decode($result->getBody()->getContents(), true);
                if (is_array($properties)) {
                    $this->logService->logWiredmindsConnectionSuccess($visitor);
                    return $properties;
                }
            }
        } catch (Throwable $exception) {
        }
        return [];
    }

    public function getStatus(): array
    {
        try {
            $result = $this->requestFactory->request($this->getUriForStatus());
            if ($result->getStatusCode() === 200) {
                $properties = json_decode($result->getBody()->getContents(), true);
                if (is_array($properties)) {
                    return $properties;
                }
            }
        } catch (Throwable $exception) {
        }
        return [];
    }

    public function getStatusForToken(string $token): array
    {
        try {
            $result = $this->requestFactory->request($this->getUriForStatus($token));
            if ($result->getStatusCode() === 200) {
                $properties = json_decode($result->getBody()->getContents(), true);
                if (is_array($properties)) {
                    return $properties;
                }
            }
        } catch (Throwable $exception) {
        }
        return [];
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
        $token = trim($this->settings['tracking']['company']['token'] ?? '');
        if ($token === '') {
            throw new ConfigurationException('No wiredminds token defined in TypoScript', 1684433462);
        }
        return self::INTERFACE_URL . $token . '/' . $ipAddress;
    }

    /**
     * @param string $token
     * @return string
     * @throws ConfigurationException
     */
    protected function getUriForStatus(string $token = ''): string
    {
        if ($token === '') {
            $token = trim($this->settings['tracking']['company']['token'] ?? '');
        }
        if ($token === '') {
            throw new ConfigurationException('No wiredminds token defined in TypoScript', 1686560916);
        }
        return self::INTERFACE_URL . $token . '/status';
    }

    /**
     * Check for limit per month (defined in TypoScript) and a general maximum limit
     *
     * @return bool
     */
    protected function isConnectionLimitReached(): bool
    {
        $logsOfCurrentMonth = $this->logRepository->findAmountOfWiredmindsLogsOfCurrentMonth();
        return $logsOfCurrentMonth >= self::LIMIT_MONTH_ABSOLUTE
            || $logsOfCurrentMonth >= (int)($this->settings['tracking']['company']['connectionLimit'] ?? 0);
    }
}

<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository\Remote;

use In2code\Lux\Domain\Model\Visitor;
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
    protected VisitorRepository $visitorRepository;
    protected RequestFactory $requestFactory;
    protected LogService $logService;

    protected array $settings = [];

    public function __construct(
        VisitorRepository $visitorRepository,
        RequestFactory $requestFactory,
        LogService $logService
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->requestFactory = $requestFactory;
        $this->logService = $logService;
        $configurationService = ObjectUtility::getConfigurationService();
        $this->settings = $configurationService->getTypoScriptSettings();
    }

    public function getPropertiesForIpAddress(Visitor $visitor, string $ipAddress = ''): array
    {
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
     * @return string
     * @throws ConfigurationException
     */
    protected function getUriForStatus(): string
    {
        $token = trim($this->settings['tracking']['company']['token'] ?? '');
        if ($token === '') {
            throw new ConfigurationException('No wiredminds token defined in TypoScript', 1686560916);
        }
        return self::INTERFACE_URL . $token . '/status';
    }
}

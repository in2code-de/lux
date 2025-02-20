<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Exception\FingerprintMustNotBeEmptyException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\EnvironmentUtility;
use In2code\Lux\Utility\IpUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WhichBrowser\Parser;

class Fingerprint extends AbstractModel
{
    public const TABLE_NAME = 'tx_lux_domain_model_fingerprint';
    public const TYPE_FINGERPRINT = 0;
    public const TYPE_COOKIE = 1;
    public const TYPE_STORAGE = 2;
    public const IDENTIFICATOR_LENGTH_FINGERPRINT = 32;
    public const IDENTIFICATOR_LENGTH_STORAGE = 33;
    public const IDENTIFICATOR_LENGTH_FINGERPRINTWITHIPHASH = 64;

    protected string $value = '';
    protected string $domain = '';
    protected string $userAgent = '';
    protected string $site = '';
    protected int $type = self::TYPE_FINGERPRINT;

    public function __construct(string $domain = '', string $userAgent = '')
    {
        parent::__construct();
        if ($this->domain === '') {
            $this->domain = ($domain !== '' ? $domain : GeneralUtility::getIndpEnv('HTTP_HOST'));
        }
        if ($this->userAgent === '') {
            $this->userAgent = ($userAgent !== '' ? $userAgent : GeneralUtility::getIndpEnv('HTTP_USER_AGENT'));
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     * @throws FingerprintMustNotBeEmptyException
     */
    public function setValue(string $value): self
    {
        if ($value === '') {
            throw new FingerprintMustNotBeEmptyException('Value is empty', 1585901797);
        }
        if (strlen($value) === self::IDENTIFICATOR_LENGTH_STORAGE) {
            $this->setType(self::TYPE_STORAGE);
            $this->value = $value;
        } else {
            $this->value = hash('sha256', $value . IpUtility::getIpAddress());
        }
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getPropertiesFromUserAgent(): array
    {
        $properties = [
            'browser' => '',
            'browserversion' => '',
            'os' => '',
            'osversion' => '',
            'manufacturer' => '',
            'type' => '',
        ];
        if (class_exists(Parser::class)) {
            $parser = new Parser($this->getUserAgent());
            $properties = [
                'browser' => $parser->browser->getName() ?? '',
                'browserversion' => (string)($parser->browser->version->value ?? ''),
                'os' => $parser->os->getName() ?? '',
                'osversion' => $parser->os->getVersion() ?? '',
                'manufacturer' => $parser->device->getManufacturer() ?? '',
                'type' => $parser->device->type ?? '',
            ];
        }
        return $properties;
    }

    public function getUserAgentBrowser(): string
    {
        return $this->getPropertiesFromUserAgent()['browser'];
    }

    public function getUserAgentBrowserversion(): string
    {
        return $this->getPropertiesFromUserAgent()['browserversion'];
    }

    public function getUserAgentOs(): string
    {
        return $this->getPropertiesFromUserAgent()['os'];
    }

    public function getUserAgentOsversion(): string
    {
        return $this->getPropertiesFromUserAgent()['osversion'];
    }

    public function getUserAgentManufacturer(): string
    {
        return $this->getPropertiesFromUserAgent()['manufacturer'];
    }

    public function getUserAgentType(): string
    {
        return $this->getPropertiesFromUserAgent()['type'];
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function setSite(string $site): self
    {
        $this->site = $site;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTypeString(): string
    {
        if ($this->getType() === self::TYPE_FINGERPRINT) {
            return 'Fingerprint';
        }
        if ($this->getType() === self::TYPE_COOKIE) {
            return 'Cookie';
        }
        if ($this->getType() === self::TYPE_STORAGE) {
            return 'LocalStorage';
        }
        return 'Unknown';
    }

    /**
     * Check if this record can be viewed by current editor
     *
     * @return bool
     */
    public function canBeRead(): bool
    {
        if (EnvironmentUtility::isBackend() === false || BackendUtility::isAdministrator() || $this->site === '') {
            return true;
        }
        $sites = GeneralUtility::makeInstance(SiteService::class)->getAllowedSites();
        return array_key_exists($this->getSite(), $sites);
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Category
 */
class Idcookie extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_idcookie';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var string
     */
    protected $userAgent = '';

    /**
     * Idcookie constructor.
     * @param string $domain
     * @param string $userAgent
     */
    public function __construct(string $domain = '', string $userAgent = '')
    {
        if ($this->domain === '') {
            $this->domain = ($domain !== '' ? $domain : GeneralUtility::getIndpEnv('HTTP_HOST'));
        }
        if ($this->userAgent === '') {
            $this->userAgent = ($userAgent !== '' ? $userAgent : GeneralUtility::getIndpEnv('HTTP_USER_AGENT'));
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Idcookie
     */
    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     * @return Idcookie
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return array
     */
    public function getPropertiesFromUserAgent(): array
    {
        $properties = [
            'browser' => '',
            'browserversion' => '',
            'os' => '',
            'osversion' => '',
            'manufacturer' => '',
            'type' => ''
        ];
        try {
            $parser = new \WhichBrowser\Parser($this->getUserAgent());
            $properties = [
                'browser' => $parser->browser->getName(),
                'browserversion' => $parser->browser->version->value,
                'os' => $parser->os->getName(),
                'osversion' => $parser->os->getVersion(),
                'manufacturer' => $parser->device->getManufacturer(),
                'type' => $parser->device->type
            ];
        } catch (\Exception $exception) {
        }
        return $properties;
    }
}

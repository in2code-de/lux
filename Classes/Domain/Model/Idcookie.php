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
     * Idcookie constructor.
     * @param string $domain
     */
    public function __construct(string $domain = '')
    {
        if ($this->domain === '') {
            $this->domain = !empty($domain) ? $domain : GeneralUtility::getIndpEnv('HTTP_HOST');
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
}

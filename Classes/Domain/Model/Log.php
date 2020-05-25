<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

/**
 * Class Log
 */
class Log extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_log';
    const STATUS_DEFAULT = 0;
    const STATUS_NEW = 1;
    const STATUS_IDENTIFIED = 2; // Fieldlistening
    const STATUS_IDENTIFIED_FORMLISTENING = 25;
    const STATUS_IDENTIFIED_LUXLETTERLINK = 28;
    const STATUS_IDENTIFIED_FRONTENDAUTHENTICATION = 26;
    const STATUS_IDENTIFIED_EMAIL4LINK = 21;
    const STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAIL = 22;
    const STATUS_IDENTIFIED_EMAIL4LINK_SENDEMAILFAILED = 23;
    const STATUS_ATTRIBUTE = 3;
    const STATUS_PAGEVISIT2 = 40;
    const STATUS_PAGEVISIT3 = 41;
    const STATUS_PAGEVISIT4 = 42;
    const STATUS_PAGEVISIT5 = 43;
    const STATUS_DOWNLOAD = 50;
    const STATUS_ACTION = 60;
    const STATUS_CONTEXTUAL_CONTENT = 70;
    const STATUS_LINKLISTENER = 80;
    const STATUS_MERGE_BYFINGERPRINT = 90;
    const STATUS_MERGE_BYEMAIL = 91;

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @var int
     */
    protected $status = 0;

    /**
     * @var \DateTime|null
     */
    protected $crdate = null;

    /**
     * @var string
     */
    protected $properties = '';

    /**
     * @return Visitor
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Log
     */
    public function setVisitor(Visitor $visitor): Log
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Log
     */
    public function setStatus(int $status): Log
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCrdate(): \DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return Log
     */
    public function setCrdate(\DateTime $crdate): Log
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return json_decode($this->properties, true);
    }

    /**
     * @param array $properties
     * @return Log
     */
    public function setProperties(array $properties): Log
    {
        $this->properties = json_encode($properties);
        return $this;
    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        return ltrim($this->getPropertyByKey('href'), '/');
    }

    /**
     * @return string
     */
    public function getWorkflowTitle(): string
    {
        return $this->getPropertyByKey('workflowTitle');
    }

    /**
     * @return string
     */
    public function getShownContentUid(): string
    {
        return $this->getPropertyByKey('shownContentUid');
    }

    /**
     * @return string
     */
    public function getPageUid(): string
    {
        return $this->getPropertyByKey('pageUid');
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->getPropertyByKey('tag');
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getPropertyByKey(string $key): string
    {
        $property = '';
        $properties = $this->getProperties();
        if (array_key_exists($key, $properties)) {
            $property = (string)$properties[$key];
        }
        return $property;
    }

    /**
     * Get all status codes that shows an identification
     *
     * @return array
     */
    public static function getIdentifiedStatus(): array
    {
        return [
            Log::STATUS_IDENTIFIED,
            Log::STATUS_IDENTIFIED_FORMLISTENING,
            Log::STATUS_IDENTIFIED_LUXLETTERLINK,
            Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION,
            Log::STATUS_IDENTIFIED_EMAIL4LINK,
        ];
    }
}

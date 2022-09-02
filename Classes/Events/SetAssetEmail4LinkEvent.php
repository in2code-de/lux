<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Core\Mail\MailMessage;

final class SetAssetEmail4LinkEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var string
     */
    protected $href = '';

    /**
     * @param Visitor $visitor
     * @param MailMessage $mailMessage
     * @param string $href
     */
    public function __construct(Visitor $visitor, MailMessage $mailMessage, string $href)
    {
        $this->visitor = $visitor;
        $this->mailMessage = $mailMessage;
        $this->href = $href;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return MailMessage
     */
    public function getMailMessage(): MailMessage
    {
        return $this->mailMessage;
    }

    /**
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }
}

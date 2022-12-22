<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use In2code\Lux\Domain\Model\Visitor;
use TYPO3\CMS\Core\Mail\MailMessage;

final class SetAssetEmail4LinkEvent
{
    protected Visitor $visitor;
    protected MailMessage $mailMessage;

    protected string $href = '';

    public function __construct(Visitor $visitor, MailMessage $mailMessage, string $href)
    {
        $this->visitor = $visitor;
        $this->mailMessage = $mailMessage;
        $this->href = $href;
    }

    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    public function getMailMessage(): MailMessage
    {
        return $this->mailMessage;
    }

    public function getHref(): string
    {
        return $this->href;
    }
}

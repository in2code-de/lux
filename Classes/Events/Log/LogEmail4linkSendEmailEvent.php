<?php

declare(strict_types=1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Visitor;

final class LogEmail4linkSendEmailEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var string
     */
    protected $href = '';

    /**
     * Constructor
     *
     * @param Visitor $visitor
     * @param string $href
     */
    public function __construct(Visitor $visitor, string $href)
    {
        $this->visitor = $visitor;
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
     * @return string
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * @param string $href
     * @return LogEmail4linkSendEmailEvent
     */
    public function setHref(string $href): LogEmail4linkSendEmailEvent
    {
        $this->href = $href;
        return $this;
    }
}

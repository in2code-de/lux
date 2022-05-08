<?php
declare(strict_types = 1);
namespace In2code\Lux\Events\Log;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Visitor;

final class DownloadEvent
{
    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @var Download
     */
    protected $download;

    /**
     * @param Visitor $visitor
     * @param Download $download
     */
    public function __construct(Visitor $visitor, Download $download)
    {
        $this->visitor = $visitor;
        $this->download = $download;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @return Download
     */
    public function getDownload(): Download
    {
        return $this->download;
    }
}

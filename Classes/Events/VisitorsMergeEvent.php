<?php

declare(strict_types=1);
namespace In2code\Lux\Events;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

final class VisitorsMergeEvent
{
    protected QueryResultInterface $visitors;

    public function __construct(QueryResultInterface $visitors)
    {
        $this->visitors = $visitors;
    }

    public function getVisitors(): QueryResultInterface
    {
        return $this->visitors;
    }
}

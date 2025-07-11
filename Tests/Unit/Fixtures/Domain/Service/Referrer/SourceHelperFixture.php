<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Referrer;

use In2code\Lux\Domain\Service\Referrer\SourceHelper;

class SourceHelperFixture extends SourceHelper
{
    public function __construct(string $referrer = '')
    {
        $this->referrer = $referrer;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getKeyFromUrl(string $url): string
    {
        $host = \In2code\Lux\Utility\UrlUtility::getHostFromUrl($url);
        return $this->getKeyFromHost($host);
    }

    /**
     * Make protected method public for testing
     *
     * @param string $host
     * @return string
     */
    public function getKeyFromHost(string $host): string
    {
        return parent::getKeyFromHost($host);
    }
}

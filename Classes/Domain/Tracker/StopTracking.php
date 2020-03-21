<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Exception\BlacklistedUserAgentException;
use In2code\Lux\Domain\Model\Fingerprint;

/**
 * Class StopTracking
 * to stop the initial tracking for some reason
 */
class StopTracking
{
    /**
     * Get Browser from user_agent. Example Googlebot (user_agent => browser)
     * "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" => "Googlebot"
     * Look at Fingerprint::getPropertiesFromUserAgent() for details on how the user_agent is parsed
     *
     * @var array
     */
    protected $blacklistedBrowsers = [
        'Googlebot'
    ];

    /**
     * @param Fingerprint $fingerprint
     * @param string $referrer
     * @return void
     * @throws BlacklistedUserAgentException
     */
    public function stop(Fingerprint $fingerprint, string $referrer)
    {
        unset($referrer);
        $browser = $fingerprint->getPropertiesFromUserAgent()['browser'];
        if (in_array($browser, $this->blacklistedBrowsers)) {
            throw new BlacklistedUserAgentException('Stop tracking because of blacklisted browser', 1565604005);
        }
    }
}

<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Exception\BlacklistedUserAgentException;
use In2code\Lux\Domain\Model\Idcookie;

/**
 * Class StopTracking
 * to stop the initial tracking for some reason
 */
class StopTracking
{
    /**
     * Get Browser from user_agent. Example Googlebot (user_agent => browser)
     * "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" => "Googlebot"
     * Look at Idcookie::getPropertiesFromUserAgent() for details on how the user_agent is parsed
     *
     * @var array
     */
    protected $blacklistedBrowsers = [
        'Googlebot'
    ];

    /**
     * @param Idcookie $idcookie
     * @param string $referrer
     * @return void
     * @throws BlacklistedUserAgentException
     */
    public function stop(Idcookie $idcookie, string $referrer)
    {
        unset($referrer);
        $browser = $idcookie->getPropertiesFromUserAgent()['browser'];
        if (in_array($browser, $this->blacklistedBrowsers)) {
            throw new BlacklistedUserAgentException('Stop tracking because of blacklisted browser', 1565604005);
        }
    }
}

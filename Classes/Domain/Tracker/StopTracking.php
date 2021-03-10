<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Exception\DisallowedUserAgentException;
use In2code\Lux\Domain\Model\Fingerprint;

/**
 * Class StopTracking
 *
 * to stop the initial tracking for some reasons:
 * - If useragent is empty (seems to be not a normal visitor)
 * - If useragent turns out to be a bot (via WhichBrowser\Parser)
 * - If useragent turns out to be a blacklisted browser (e.g. "Googlebot")
 * - If useragent contains stop words (e.g. lighthouse, sistrix)
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
     * Search in complete UserAgent string for a string and stop tracking if found
     *
     * @var array
     */
    protected $blacklistedUa = [
        'googlebot',
        'pinterestbot',
        'linkedinbot',
        'bingbot',
        'archive.org_bot',
        'yandexbot',
        'sistrix',
        'lighthouse',
        'sistrix',
        'cookieradar'
    ];

    /**
     * Stop tracking if:
     * - UserAgent is empty (probably a crawler like crawler or caretaker extension in TYPO3)
     * - For any blacklisted strings in UserAgent string
     * - For any browsers (parsed UserAgent)
     *
     * @param Fingerprint $fingerprint
     * @return void Throw exception if blacklisted
     * @throws DisallowedUserAgentException
     */
    public function stop(Fingerprint $fingerprint)
    {
        $this->checkForEmptyUserAgent($fingerprint);
        $this->checkForBotUserAgent($fingerprint);
        $this->checkForBlacklistedParsedUserAgent($fingerprint);
        $this->checkForBlacklistedUserAgentStrings($fingerprint);
    }

    /**
     * @param Fingerprint $fingerprint
     * @return void
     * @throws DisallowedUserAgentException
     */
    protected function checkForEmptyUserAgent(Fingerprint $fingerprint): void
    {
        if ($fingerprint->getUserAgent() === '') {
            throw new DisallowedUserAgentException('Stop tracking because of empty user agent', 1592581081);
        }
    }

    /**
     * @param Fingerprint $fingerprint
     * @return void
     * @throws DisallowedUserAgentException
     */
    protected function checkForBotUserAgent(Fingerprint $fingerprint): void
    {
        if ($fingerprint->getPropertiesFromUserAgent()['type'] === 'bot') {
            throw new DisallowedUserAgentException('Stop tracking because of bot', 1608109683);
        }
    }

    /**
     * @param Fingerprint $fingerprint
     * @return void
     * @throws DisallowedUserAgentException
     */
    protected function checkForBlacklistedParsedUserAgent(Fingerprint $fingerprint): void
    {
        $browser = $fingerprint->getPropertiesFromUserAgent()['browser'];
        if (in_array($browser, $this->blacklistedBrowsers)) {
            throw new DisallowedUserAgentException('Stop tracking because of blacklisted browser', 1565604005);
        }
    }

    /**
     * @param Fingerprint $fingerprint
     * @return void
     * @throws DisallowedUserAgentException
     */
    protected function checkForBlacklistedUserAgentStrings(Fingerprint $fingerprint): void
    {
        foreach ($this->blacklistedUa as $userAgentPart) {
            if (stristr($fingerprint->getUserAgent(), $userAgentPart) !== false) {
                throw new DisallowedUserAgentException('Stop tracking because of blacklisted user agent', 1592581260);
            }
        }
    }
}

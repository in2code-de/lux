<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent;
use In2code\Lux\Exception\DisallowedIpAddressException;
use In2code\Lux\Exception\DisallowedUserAgentException;
use In2code\Lux\Utility\IpUtility;

/**
 * Class StopTracking
 *
 * to stop the initial tracking for some reason:
 * - If useragent is empty (seems to be not a normal visitor)
 * - If useragent contains stop words (e.g. lighthouse, sistrix)
 * - If useragent turns out to be a blacklisted browser (e.g. "Googlebot")
 * - If useragent turns out to be a bot (via WhichBrowser\Parser)
 * - If IP address is in blacklisted range
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
    protected array $blacklistedBrowsers = [
        'Googlebot',
    ];

    /**
     * Search in complete UserAgent string for a string and stop tracking if found
     *
     * @var array
     */
    protected array $blacklistedUa = [
        'adidxbot',
        'adsbot-google',
        'ahrefsbot',
        'alexabot',
        'amazonbot',
        'anthropic-ai',
        'applebot',
        'archive.org_bot',
        'awariorssbot',
        'awariosmartbot',
        'baiduspider',
        'bard',
        'bingbot',
        'blexbot',
        'bytespider',
        'ccbot',
        'chatgpt',
        'claude',
        'claudebot',
        'claude-web',
        'cookieradar',
        'cohere-ai',
        'copyai',
        'curl',
        'dataforseobot',
        'deepai',
        'discordbot',
        'diffbot',
        'dotbot',
        'duckduckbot',
        'exabot',
        'facebookexternalhit',
        'facebookbot',
        'friendlycrawler',
        'google-extended',
        'googlebot',
        'googleother',
        'gptbot',
        'headlesschrome',
        'huggingface',
        'ia_archiver',
        'imagesiftbot',
        'img2dataset',
        'jasper',
        'lighthouse',
        'linkedinbot',
        'llama',
        'mauibot',
        'magpie-crawler',
        'meltwater',
        'msnbot',
        'omgili',
        'omgilibot',
        'openai-gpt',
        'peer39_crawler',
        'perplexityai',
        'perplexitybot',
        'phantomjs',
        'pingdom',
        'pinterestbot',
        'piplbot',
        'python-requests',
        'quora poe',
        'rogerbot',
        'sage',
        'seekr',
        'scoop.it',
        'selenium',
        'semrushbot',
        'sistrix',
        'skypebot',
        'slackbot',
        'slurp',
        'sogou',
        'telegrambot',
        'twitterbot',
        'uptimerobot',
        'wget',
        'whatsapp',
        'yacybot',
        'yandexbot',
        'youbot',
        'youchat',
    ];

    /**
     * List e.g. from https://www.perplexity.ai/perplexitybot.json
     *
     * @var array
     */
    protected array $ipRanges = [
        '54.90.207.250/32',
        '23.22.208.105/32',
        '54.242.1.13/32',
        '18.208.251.246/32',
        '34.230.5.59/32',
        '18.207.114.171/32',
        '54.221.7.250/32',
    ];

    /**
     * Stop tracking if:
     * - UserAgent is empty (probably a crawler like crawler or caretaker extension in TYPO3)
     * - For any blacklisted strings in UserAgent string
     * - For any browsers (parsed UserAgent)
     *
     * @param StopAnyProcessBeforePersistenceEvent $event
     * @return void Throw exception if blacklisted
     * @throws DisallowedUserAgentException
     * @throws DisallowedIpAddressException
     */
    public function __invoke(StopAnyProcessBeforePersistenceEvent $event)
    {
        $this->checkForEmptyUserAgent($event->getFingerprint());
        $this->checkForBlacklistedUserAgentStrings($event->getFingerprint());
        $this->checkForBlacklistedParsedUserAgent($event->getFingerprint());
        $this->checkForBotUserAgent($event->getFingerprint());
        $this->checkForBlacklistedIpAddressRanges();
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
    protected function checkForBlacklistedUserAgentStrings(Fingerprint $fingerprint): void
    {
        foreach ($this->blacklistedUa as $userAgentPart) {
            if (stristr($fingerprint->getUserAgent(), $userAgentPart) !== false) {
                throw new DisallowedUserAgentException('Stop tracking because of blacklisted user agent', 1592581260);
            }
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
    protected function checkForBotUserAgent(Fingerprint $fingerprint): void
    {
        if ($fingerprint->getPropertiesFromUserAgent()['type'] === 'bot') {
            throw new DisallowedUserAgentException('Stop tracking because of bot', 1608109683);
        }
    }

    /**
     * @return void
     * @throws DisallowedIpAddressException
     */
    protected function checkForBlacklistedIpAddressRanges(): void
    {
        if (IpUtility::isCurrentIpInGivenRanges($this->ipRanges)) {
            throw new DisallowedIpAddressException('Stop tracking because of blacklisted IP', 1723793497);
        }
    }
}

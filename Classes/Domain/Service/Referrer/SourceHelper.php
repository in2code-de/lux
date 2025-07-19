<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Referrer;

use In2code\Lux\Events\ReadableReferrersEvent;
use In2code\Lux\Utility\LocalizationUtility;
use In2code\Lux\Utility\UrlUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SourceHelper
 * converts referrers like m.twitter.com to a readable referrer like "Twitter"
 */
class SourceHelper
{
    protected array $sources = [
        'social' => [
            [
                'label' => 'X (Twitter)',
                'domains' => [
                    'ads-twitter.com',
                    'periscope.tv',
                    'pscp.tv',
                    't.co',
                    'tweetdeck.com',
                    'twimg.com',
                    'twitpic.com',
                    'twitter.co',
                    'twitter.com',
                    'twitterinc.com',
                    'twitteroauth.com',
                    'twitterstat.us',
                    'twtrdns.net',
                    'twttr.com',
                    'x.com',
                ],
            ],
            [
                'label' => 'Facebook',
                'domains' => [
                    'facebook.com',
                    'facebook.net',
                    'fb.com',
                    'fbcdn.net',
                    'fbpigeon.com',
                    'fbsbx.com',
                    'www.facebook.com',
                ],
            ],
            [
                'label' => 'Instagram',
                'domains' => [
                    'cdninstagram.com',
                    'ig.me',
                    'instagr.am',
                    'instagram.com',
                ],
            ],
            [
                'label' => 'LinkedIn',
                'domains' => [
                    'l.linkedin.com',
                    'licdn.cn',
                    'licdn.com',
                    'linkedin.cn',
                    'linkedin.com',
                    'lm.linkedin.com',
                    'lnkd.in',
                    'm.linkedin.com',
                    'www.linkedin.com',
                ],
            ],
            [
                'label' => 'XING',
                'domains' => [
                    'www.xing.com',
                    'xing.com',
                ],
            ],
            [
                'label' => 'YouTube',
                'domains' => [
                    'www.youtube.com',
                    'youtube.com',
                ],
            ],
            [
                'label' => 'Vimeo',
                'domains' => [
                    'vhx.com',
                    'vhx.tv',
                    'vimeo.com',
                    'vimeo.com',
                    'vimeocdn.com',
                ],
            ],
            [
                'label' => 'Pinterest',
                'domains' => [
                    '2-01-37d2-0018.cdx.cedexis.net',
                    'pin.it',
                    'pinimg.com',
                    'pinterest.com',
                    'pinterest.info',
                    'pinterest.net',
                    'pinterestmail.com',
                    'www.pinterest.com',
                ],
            ],
            [
                'label' => 'TikTok',
                'domains' => [
                    'www.tiktok.com',
                    'vm.tiktok.com',
                ],
            ],
            [
                'label' => 'Snapchat',
                'domains' => [
                    'www.snapchat.com',
                ],
            ],
            [
                'label' => 'Reddit',
                'domains' => [
                    'redd.it',
                    'www.reddit.com',
                ],
            ],
            [
                'label' => 'Tumblr',
                'domains' => [
                    't.umblr.com',
                    'www.tumblr.com',
                ],
            ],
        ],
        'search' => [
            [
                'label' => 'Google Organic',
                'domains' => [
                    'www.google.at',
                    'www.google.ch',
                    'www.google.com',
                    'www.google.de',
                    'www.google.fr',
                    'www.google.it',
                ],
            ],
            [
                'label' => 'Microsoft Bing',
                'domains' => [
                    'bing.com',
                ],
            ],
            [
                'label' => 'DuckDuckGo',
                'domains' => [
                    'duckduckgo.com',
                ],
            ],
            [
                'label' => 'Yahoo',
                'domains' => [
                    'www.yahoo.com',
                    'search.yahoo.com',
                ],
            ],
            [
                'label' => 'Yandex',
                'domains' => [
                    'yandex.com',
                ],
            ],
            [
                'label' => 'Baidu',
                'domains' => [
                    'www.baidu.com',
                ],
            ],
        ],
        'email' => [
            [
                'label' => 'Mailchimp',
                'domains' => [
                    'campaign-archive.com',
                    'mailchimp.com',
                ],
            ],
            [
                'label' => 'Gmail',
                'domains' => [
                    'gmail.com',
                ],
            ],
            [
                'label' => 'Outlook',
                'domains' => [
                    'outlook.com',
                ],
            ],
        ],
        'ads' => [
            [
                'label' => 'Google Ads',
                'domains' => [
                    'ads.google.com',
                    'googleads.g.doubleclick.net',
                    'syndicatedsearch.goog',
                    'www.adsensecustomsearchads.com',
                    'www.googleadservices.com',
                ],
            ],
            [
                'label' => 'Microsoft Ads',
                'domains' => [
                    'ads.microsoft.com',
                ],
            ],
        ],
        'eCommerce' => [
            [
                'label' => 'Shopify',
                'domains' => [
                    'shopify.com',
                ],
            ],
            [
                'label' => 'Amazon',
                'domains' => [
                    'amazon.com',
                ],
            ],
        ],
        'aiChats' => [
            [
                'label' => 'ChatGPT',
                'domains' => [
                    'chat.openai.com',
                    'chatgpt.com',
                ],
            ],
            [
                'label' => 'Google Gemini',
                'domains' => [
                    'bard.google.com',
                    'gemini.google.com',
                ],
            ],
            [
                'label' => 'Microsoft Bing Chat',
                'domains' => [
                    'bing.com/chat',
                    'copilot.microsoft.com',
                    'www.bing.com/chat',
                ],
            ],
            [
                'label' => 'Claude AI',
                'domains' => [
                    'claude.ai',
                ],
            ],
            [
                'label' => 'Perplexity AI',
                'domains' => [
                    'perplexity.ai',
                    'www.perplexity.ai',
                ],
            ],
            [
                'label' => 'Mistral',
                'domains' => [
                    'chat.mistral.ai',
                ],
            ],
        ],
        'other' => [
            [
                'label' => 'Cermat',
                'domains' => [
                    'cermat.de',
                ],
            ],
            [
                'label' => 'in2code GmbH',
                'domains' => [
                    'www.in2code.de',
                ],
            ],
            [
                'label' => 'Stack Overflow',
                'domains' => [
                    'stackoverflow.com',
                ],
            ],
            [
                'label' => 'TYPO3',
                'domains' => [
                    'www.typo3.com',
                    'www.typo3.org',
                ],
            ],
        ],
    ];
    protected string $referrer = '';
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(string $referrer = '')
    {
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        /** @var ReadableReferrersEvent $event */
        $event = $this->eventDispatcher->dispatch(new ReadableReferrersEvent($referrer, $this->sources));
        $this->referrer = $event->getReferrer();
        $this->sources = $event->getSources();
    }

    public function getReadableReferrer(string $domainFromReferrer = ''): string
    {
        if ($domainFromReferrer === '') {
            $domainFromReferrer = $this->getDomain();
        }
        foreach ($this->sources as $category) {
            foreach ($category as $service) {
                foreach ($service['domains'] as $domain) {
                    if ($domain === $domainFromReferrer) {
                        return $service['label'];
                    }
                }
            }
        }
        return $domainFromReferrer;
    }

    public function getDomainsFromCategory(string $categoryKey): array
    {
        $domains = [];
        if (isset($this->sources[$categoryKey])) {
            foreach ($this->sources[$categoryKey] as $service) {
                $domains = array_merge($domains, $service['domains']);
            }
        }
        return $domains;
    }

    /**
     * @param string $host e.g. "openai.com"
     * @return string e.g. "aiChats"
     */
    public function getKeyFromHost(string $host): string
    {
        foreach ($this->sources as $categoryKey => $category) {
            foreach ($category as $service) {
                foreach ($service['domains'] as $serviceHost) {
                    if ($serviceHost === $host) {
                        return $categoryKey;
                    }
                }
            }
        }

        return '';
    }

    /**
     *  [
     *      'socialMedia' => 'Social Media',
     *      'searchEngines' => 'Search Engines',
     *      'other' => 'Other',
     *  ]
     *
     * @param bool $addOther
     * @return array
     */
    public function getAllKeys(bool $addOther = false): array
    {
        $keys = [];
        foreach (array_keys($this->sources) as $key) {
            if ($addOther === false && $key === 'other') {
                continue;
            }
            $keys[$key] = LocalizationUtility::translateByKey('readablereferrer.' . $key);
        }
        asort($keys);

        // Ensure "other" key is always the last key
        if (isset($keys['other'])) {
            $otherValue = $keys['other'];
            unset($keys['other']);
            $keys['other'] = $otherValue;
        }

        return $keys;
    }

    /**
     *  [
     *      'socialMedia' => [
     *          'www.twitter.com',
     *          'x.com',
     *          'facebook.com',
     *      ],
     *      'aiChats' => [...],
     *  ]
     *
     * @return array
     */
    public function getAllKeysWithDomains(): array
    {
        $keyWithDomains = [];
        foreach (array_keys($this->sources) as $key) {
            $keyWithDomains[$key] = array_merge($keyWithDomains[$key] ?? [], $this->getDomainsFromCategory($key));
        }
        return $keyWithDomains;
    }

    /**
     *  [
     *      'socialMedia' => 'www\.twitter\.com|x\.com|facebook\.com',
     *      'aiChats' => '...',
     *  ]
     *
     * @return array
     */
    public function getAllKeysWithDomainsForQuery(): array
    {
        $keyWithDomains = $this->getAllKeysWithDomains();
        $keyQuery = [];
        foreach ($keyWithDomains as $key => $domains) {
            $keyQuery[$key] = implode('|', array_map(fn ($domain) => preg_quote($domain, '/'), $domains));
        }
        return $keyQuery;
    }

    public function getOriginalReferrer(): string
    {
        return $this->referrer;
    }

    protected function getDomain(): string
    {
        return UrlUtility::getHostFromUrl($this->referrer);
    }
}

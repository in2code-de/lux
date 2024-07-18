<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Referrer;

use In2code\Lux\Events\ReadableReferrersEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Readable
 * converts referrers like m.twitter.com to a readable referrer like "Twitter"
 */
class Readable
{
    protected array $sources = [
        'socialMedia' => [
            [
                'label' => 'X (Twitter)',
                'domains' => [
                    't.co',
                    'www.twitter.com',
                    'm.twitter.com',
                    'l.twitter.com',
                    'lm.twitter.com',
                ],
            ],
            [
                'label' => 'Facebook',
                'domains' => [
                    'www.facebook.com',
                    'm.facebook.com',
                    'l.facebook.com',
                    'lm.facebook.com',
                ],
            ],
            [
                'label' => 'Instagram',
                'domains' => [
                    'www.instagram.com',
                    'm.instagram.com',
                    'l.instagram.com',
                    'lm.instagram.com',
                    'mobile.instagram.com',
                    'web.instagram.com',
                ],
            ],
            [
                'label' => 'LinkedIn',
                'domains' => [
                    'lnkd.in',
                    'www.linkedin.com',
                    'm.linkedin.com',
                    'l.linkedin.com',
                    'lm.linkedin.com',
                ],
            ],
            [
                'label' => 'XING',
                'domains' => [
                    'xing.com',
                    'www.xing.com',
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
                    'vimeo.com',
                    'www.vimeo.com',
                ],
            ],
            [
                'label' => 'Pinterest',
                'domains' => [
                    'www.pinterest.com',
                    'pin.it',
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
                    'www.reddit.com',
                ],
            ],
            [
                'label' => 'Tumblr',
                'domains' => [
                    'www.tumblr.com',
                    't.umblr.com',
                ],
            ],
        ],
        'searchEngines' => [
            [
                'label' => 'Google Organic',
                'domains' => [
                    'www.google.at',
                    'www.google.com',
                    'www.google.ch',
                    'www.google.de',
                    'www.google.fr',
                    'www.google.it',
                ],
            ],
            [
                'label' => 'Google AdSense',
                'domains' => [
                    'www.adsensecustomsearchads.com',
                    'syndicatedsearch.goog',
                    'googleads.g.doubleclick.net',
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
        'emailMarketing' => [
            [
                'label' => 'Mailchimp',
                'domains' => [
                    'mailchimp.com',
                    'campaign-archive.com',
                ],
            ],
            [
                'label' => 'Constant Contact',
                'domains' => [
                    'constantcontact.com',
                ],
            ],
            [
                'label' => 'SendGrid',
                'domains' => [
                    'sendgrid.com',
                ],
            ],
        ],
        'contentMarketingAndSEO' => [
            [
                'label' => 'Medium',
                'domains' => [
                    'medium.com',
                ],
            ],
            [
                'label' => 'WordPress',
                'domains' => [
                    'wordpress.com',
                ],
            ],
            [
                'label' => 'Blogger',
                'domains' => [
                    'blogger.com',
                ],
            ],
            [
                'label' => 'Moz',
                'domains' => [
                    'moz.com',
                ],
            ],
            [
                'label' => 'Ahrefs',
                'domains' => [
                    'ahrefs.com',
                ],
            ],
            [
                'label' => 'Semrush',
                'domains' => [
                    'semrush.com',
                ],
            ],
        ],
        'advertisingPlatforms' => [
            [
                'label' => 'Google Ads',
                'domains' => [
                    'ads.google.com',
                ],
            ],
            [
                'label' => 'Amazon Advertising',
                'domains' => [
                    'advertising.amazon.com',
                ],
            ],
            [
                'label' => 'Microsoft Advertising',
                'domains' => [
                    'ads.microsoft.com',
                ],
            ],
        ],
        'analyticsAndTracking' => [
            [
                'label' => 'Google Analytics',
                'domains' => [
                    'analytics.google.com',
                ],
            ],
            [
                'label' => 'Matomo Analytics',
                'domains' => [
                    'matomo.org',
                ],
            ],
            [
                'label' => 'Mixpanel',
                'domains' => [
                    'mixpanel.com',
                ],
            ],
        ],
        'crmAndMarketingAutomation' => [
            [
                'label' => 'Salesforce',
                'domains' => [
                    'salesforce.com',
                ],
            ],
            [
                'label' => 'HubSpot',
                'domains' => [
                    'hubspot.com',
                ],
            ],
            [
                'label' => 'Marketo',
                'domains' => [
                    'marketo.com',
                ],
            ],
        ],
        'webinarsAndOnlineEvents' => [
            [
                'label' => 'Zoom',
                'domains' => [
                    'zoom.us',
                ],
            ],
            [
                'label' => 'Webex',
                'domains' => [
                    'webex.com',
                ],
            ],
            [
                'label' => 'GoToMeeting',
                'domains' => [
                    'gotomeeting.com',
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
                'label' => 'WooCommerce',
                'domains' => [
                    'woocommerce.com',
                ],
            ],
            [
                'label' => 'Magento',
                'domains' => [
                    'magento.com',
                ],
            ],
        ],
        'affiliateMarketing' => [
            [
                'label' => 'ClickBank',
                'domains' => [
                    'clickbank.com',
                ],
            ],
            [
                'label' => 'ShareASale',
                'domains' => [
                    'shareasale.com',
                ],
            ],
            [
                'label' => 'Commission Junction',
                'domains' => [
                    'cj.com',
                ],
            ],
        ],
        'messagingAndChat' => [
            [
                'label' => 'WhatsApp',
                'domains' => [
                    'whatsapp.com',
                ],
            ],
            [
                'label' => 'Telegram',
                'domains' => [
                    'telegram.org',
                ],
            ],
            [
                'label' => 'Facebook Messenger',
                'domains' => [
                    'messenger.com',
                ],
            ],
            [
                'label' => 'Slack',
                'domains' => [
                    'com.slack',
                    'slack.com',
                ],
            ],
        ],
        'professionalNetworks' => [
            [
                'label' => 'GitHub',
                'domains' => [
                    'github.com',
                ],
            ],
            [
                'label' => 'Stack Overflow',
                'domains' => [
                    'stackoverflow.com',
                ],
            ],
            [
                'label' => 'Behance',
                'domains' => [
                    'behance.net',
                ],
            ],
            [
                'label' => 'Dribbble',
                'domains' => [
                    'dribbble.com',
                ],
            ],
            [
                'label' => 'TYPO3',
                'domains' => [
                    'typo3.org',
                    'typo3.com',
                ],
            ],
        ],
        'other' => [
            [
                'label' => 'in2code GmbH',
                'domains' => [
                    'www.in2code.de',
                ],
            ],
            [
                'label' => 'Cermat',
                'domains' => [
                    'cermat.de',
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

    public function getReadableReferrer(): string
    {
        $domainFromReferrer = $this->getDomain();
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

    public function getOriginalReferrer(): string
    {
        return $this->referrer;
    }

    protected function getDomain(): string
    {
        $domain = '';
        $parts = parse_url($this->referrer);
        if (array_key_exists('host', $parts)) {
            $domain = $parts['host'];
        }
        return $domain;
    }
}

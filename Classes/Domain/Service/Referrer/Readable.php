<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Referrer;

/**
 * Class Readable
 * converts referrers like m.twitter.com to a readable referrer like "Twitter"
 */
class Readable
{
    protected string $referrer = '';

    /**
     * Array with mapping for domains (original => Readable)
     *
     * @var array
     */
    protected array $sources = [
        // Social Media
        't.co' => 'X (Twitter)',
        'www.twitter.com' => 'X (Twitter)',
        'm.twitter.com' => 'X (Twitter)',
        'l.twitter.com' => 'X (Twitter)',
        'lm.twitter.com' => 'X (Twitter)',
        'www.facebook.com' => 'Facebook',
        'm.facebook.com' => 'Facebook',
        'l.facebook.com' => 'Facebook',
        'lm.facebook.com' => 'Facebook',
        'www.instagram.com' => 'Instagram',
        'm.instagram.com' => 'Instagram',
        'l.instagram.com' => 'Instagram',
        'lm.instagram.com' => 'Instagram',
        'mobile.instagram.com' => 'Instagram',
        'web.instagram.com' => 'Instagram',
        'lnkd.in' => 'LinkedIn',
        'www.linkedin.com' => 'LinkedIn',
        'm.linkedin.com' => 'LinkedIn',
        'l.linkedin.com' => 'LinkedIn',
        'lm.linkedin.com' => 'LinkedIn',
        'xing.com' => 'XING',
        'www.xing.com' => 'XING',
        'www.youtube.com' => 'YouTube',
        'youtube.com' => 'YouTube',
        'vimeo.com' => 'Vimeo',
        'www.vimeo.com' => 'Vimeo',
        'www.pinterest.com' => 'Pinterest',
        'pin.it' => 'Pinterest',
        'www.tiktok.com' => 'TikTok',
        'vm.tiktok.com' => 'TikTok',
        'www.snapchat.com' => 'Snapchat',
        'www.reddit.com' => 'Reddit',
        'www.tumblr.com' => 'Tumblr',
        't.umblr.com' => 'Tumblr',

        // Search Engines
        'www.google.at' => 'Google Austria',
        'www.google.com' => 'Google International',
        'www.google.ch' => 'Google Switzerland',
        'www.google.de' => 'Google Germany',
        'www.google.fr' => 'Google France',
        'www.google.it' => 'Google Italy',
        'www.adsensecustomsearchads.com' => 'Google AdSense',
        'syndicatedsearch.goog' => 'Google AdSense',
        'googleads.g.doubleclick.net' => 'Google AdSense',
        'bing.com' => 'Microsoft Bing',
        'duckduckgo.com' => 'DuckDuckGo',
        'www.yahoo.com' => 'Yahoo',
        'search.yahoo.com' => 'Yahoo Search',
        'yandex.com' => 'Yandex',
        'www.baidu.com' => 'Baidu',

        // Email Marketing
        'mailchimp.com' => 'Mailchimp',
        'constantcontact.com' => 'Constant Contact',
        'sendgrid.com' => 'SendGrid',
        'campaign-archive.com' => 'Mailchimp Campaigns',

        // Content Marketing and SEO
        'medium.com' => 'Medium',
        'wordpress.com' => 'WordPress',
        'blogger.com' => 'Blogger',
        'moz.com' => 'Moz',
        'ahrefs.com' => 'Ahrefs',
        'semrush.com' => 'Semrush',

        // Advertising Platforms
        'ads.google.com' => 'Google Ads',
        'advertising.amazon.com' => 'Amazon Advertising',
        'ads.microsoft.com' => 'Microsoft Advertising',

        // Analytics and Tracking
        'analytics.google.com' => 'Google Analytics',
        'matomo.org' => 'Matomo Analytics',
        'mixpanel.com' => 'Mixpanel',

        // CRM and Marketing Automation
        'salesforce.com' => 'Salesforce',
        'hubspot.com' => 'HubSpot',
        'marketo.com' => 'Marketo',

        // Webinars and Online Events
        'zoom.us' => 'Zoom',
        'webex.com' => 'Webex',
        'gotomeeting.com' => 'GoToMeeting',

        // E-Commerce
        'shopify.com' => 'Shopify',
        'woocommerce.com' => 'WooCommerce',
        'magento.com' => 'Magento',

        // Affiliate Marketing
        'clickbank.com' => 'ClickBank',
        'shareasale.com' => 'ShareASale',
        'cj.com' => 'Commission Junction',

        // Messaging and Chat
        'whatsapp.com' => 'WhatsApp',
        'telegram.org' => 'Telegram',
        'messenger.com' => 'Facebook Messenger',
        'com.slack' => 'Slack',
        'slack.com' => 'Slack',

        // Professional Networks
        'github.com' => 'GitHub',
        'stackoverflow.com' => 'Stack Overflow',
        'behance.net' => 'Behance',
        'dribbble.com' => 'Dribbble',
        'typo3.org' => 'TYPO3',
        'typo3.com' => 'TYPO3',

        // Other
        'www.in2code.de' => 'in2code GmbH',
        'cermat.de' => 'Cermat',
    ];

    public function __construct(string $referrer = '')
    {
        $this->referrer = $referrer;
    }

    public function getReadableReferrer(): string
    {
        $domain = $this->getDomain();
        if (array_key_exists($domain, $this->sources)) {
            $domain = $this->sources[$domain];
        }
        return $domain;
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

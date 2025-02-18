<?php

declare(strict_types=1);
namespace In2code\Lux\Hooks;

use In2code\Lux\Utility\CookieUtility;
use In2code\Luxletter\Events\LuxletterLinkLuxIdentificationEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(
    identifier: 'lux/handle-luxletter-link-lux-identification',
    event: LuxletterLinkLuxIdentificationEvent::class
)]
class LuxletterLinkLuxIdentificationHandler
{
    public function __invoke(LuxletterLinkLuxIdentificationEvent $event)
    {
        if ($event->isIdentification()) {
            $link = $event->getLink();
            CookieUtility::setCookie('luxletterlinkhash', $link['hash']);
        }
    }
}

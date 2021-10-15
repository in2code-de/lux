<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\CookieUtility;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class LuxletterlinkAttributeTracker
 */
class LuxletterlinkAttributeTracker extends AbstractFrontenduserTracker
{
    /**
     * @var string
     */
    protected $cookieName = 'luxletterlinkhash';

    /**
     * @return void
     * @throws Exception
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function trackFromLuxletterLink(): void
    {
        if (ExtensionUtility::isLuxletterVersionOrHigherAvailable('2.0.0')) {
            if (CookieUtility::getCookieByName('luxletterlinkhash') !== '') {
                $linkRepository = GeneralUtility::makeInstance(LinkRepository::class);
                /** @var Link $link */
                $link = $linkRepository->findOneByHash(CookieUtility::getCookieByName($this->cookieName));
                if ($link->getUser() !== null) {
                    $this->addOrUpdateRelation($link->getUser());
                    $this->addOrUpdateEmail($link->getUser());
                }
                CookieUtility::deleteCookie($this->cookieName);
            }
        }
    }
}

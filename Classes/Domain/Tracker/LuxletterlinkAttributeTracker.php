<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\FrontendUserRepository;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\CookieUtility;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class LuxletterlinkAttributeTracker extends AbstractFrontenduserTracker
{
    protected string $cookieName = 'luxletterlinkhash';

    /**
     * @return void
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     * @throws InvalidConfigurationTypeException
     */
    public function trackFromLuxletterLink(): void
    {
        if (ExtensionUtility::isLuxletterVersionOrHigherAvailable('2.0.0')) {
            if (CookieUtility::getCookieByName('luxletterlinkhash') !== '') {
                $linkRepository = GeneralUtility::makeInstance(LinkRepository::class);
                /** @var Link $link */
                $link = $linkRepository->findOneByHash(CookieUtility::getCookieByName($this->cookieName));
                if ($link->getUser() !== null) {
                    $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
                    $user = $userRepository->findByUid($link->getUser()->getUid());
                    $this->addOrUpdateRelation($user);
                    $this->addOrUpdateEmail($user);
                }
                CookieUtility::deleteCookie($this->cookieName);
            }
        }
    }
}

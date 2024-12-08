<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\FrontendUser;
use In2code\Lux\Domain\Repository\FrontendUserRepository;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class FrontenduserAuthenticationTracker extends AbstractFrontenduserTracker
{
    /**
     * @return void
     * @throws DBALException
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
     */
    public function trackByFrontenduserAuthentication(): void
    {
        if (FrontendUtility::isLoggedInFrontendUser()) {
            $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
            /** @var FrontendUser $user */
            $user = $userRepository->findByUid((int)FrontendUtility::getPropertyFromLoggedInFrontendUser());

            $this->setRelationToFrontendUser($user);
            $this->addEmailAttribute($user);
        }
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function setRelationToFrontendUser(FrontendUser $user): void
    {
        if ($this->visitor->getFrontenduser() === null
            || $user->getUid() !== $this->visitor->getFrontenduser()->getUid()
        ) {
            $this->addOrUpdateRelation($user);
        }
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     * @throws InvalidConfigurationTypeException
     */
    protected function addEmailAttribute(FrontendUser $user): void
    {
        if ($user->getEmail() !== null) {
            $this->addOrUpdateEmail($user);
        }
    }
}

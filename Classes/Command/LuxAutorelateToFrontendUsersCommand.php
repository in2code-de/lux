<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\FrontendUserRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LuxAutorelateToFrontendUsersCommand extends Command
{
    public function configure()
    {
        $this->setDescription(
            'Search for frontendusers and set a relation between visitor records and them'
        );
    }

    /**
     * Search for frontendusers and set a relation between visitor records and them
     *
     *      Search for frontendusers and set a relation between visitor records and them
     *
     *      Example command: ./vendor/bin/typo3 lux:autorelateToFrontendUsers
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $feuRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $usersWithEmails = $feuRepository->findFrontendUsersWithEmails();
        foreach ($usersWithEmails as $user) {
            $visitorIdentifiers = $visitorRepository->findByEmailAndEmptyFrontenduser($user['email']);
            foreach ($visitorIdentifiers as $visitorIdentifier) {
                $visitorRepository->updateVisitorWithFrontendUserRelation($visitorIdentifier, $user['uid']);
                $count++;
            }
        }
        $output->writeln($count . ' visitors updated with relation to fe_user record');
        return self::SUCCESS;
    }
}

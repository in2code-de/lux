<?php
declare(strict_types = 1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\FrontenduserRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxAutorelateToFrontendUsersCommand
 */
class LuxAutorelateToFrontendUsersCommand extends Command
{
    /**
     * @return void
     */
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
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;
        /** @var VisitorRepository $visitorRepository */
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        /** @var FrontenduserRepository $feuRepository */
        $feuRepository = GeneralUtility::makeInstance(FrontenduserRepository::class);
        $usersWithEmails = $feuRepository->findFrontendUsersWithEmails();
        foreach ($usersWithEmails as $user) {
            $visitorIdentifiers = $visitorRepository->findByEmailAndEmptyFrontenduser($user['email']);
            foreach ($visitorIdentifiers as $visitorIdentifier) {
                $visitorRepository->updateVisitorWithFrontendUserRelation($visitorIdentifier, $user['uid']);
                $count++;
            }
        }
        $output->writeln($count . ' visitors updated with relation to fe_user record');
        return 0;
    }
}

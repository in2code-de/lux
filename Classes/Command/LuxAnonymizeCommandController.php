<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Service\AnonymizeService;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class LuxCleanupCommandController
 */
class LuxAnonymizeCommandController extends CommandController
{

    /**
     * This command will really (!!!) overwrite all your records with dummy values. This command is for local
     * development or for a presentation only!
     *
     * @return void
     * @cli
     * @throws DBALException
     */
    public function anonymizeAllCommand()
    {
        /** @var AnonymizeService $anonymizeService */
        $anonymizeService = $this->objectManager->get(AnonymizeService::class);
        $anonymizeService->anonymizeAll();
        $this->outputLine('Everything was anonymized!');
    }
}

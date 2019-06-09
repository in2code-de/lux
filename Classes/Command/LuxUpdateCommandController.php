<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Service\UpdateIdcookiesService;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class LuxUpdateCommandController
 */
class LuxUpdateCommandController extends CommandController
{

    /**
     * Update visitors id_cookie to a separate table if you update from lux 1.x or 2.x to version 3.x
     *
     *      Update visitors id_cookie to a separate table if you update from lux 1.x or 2.x to version 3.x
     *
     * @param string $domainName give a domainname like "www.domain.org" for the existing cookies
     * @return void
     * @throws DBALException
     * @cli
     */
    public function removeUnknownVisitorsByAgeCommand(string $domainName)
    {
        try {
            $updateService = ObjectUtility::getObjectManager()->get(UpdateIdcookiesService::class);
            $updateService->createNewRecordsFromOldRecords($domainName);
            $message = 'update done!';
        } catch (\LogicException $exception) {
            $message = $exception->getMessage();
        }
        $this->outputLine($message);
    }
}

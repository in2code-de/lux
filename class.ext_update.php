<?php
namespace In2code\Lux;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Service\UpdateIdcookiesService;
use In2code\Lux\Utility\ObjectUtility;

/**
 * Automaticly copy tx_lux_domain_model_visitor.id_cookie to tx_lux_domain_model_idcookie
 */
class ext_update
{
    /**
     * @var UpdateIdcookiesService
     */
    protected $updateCookiesService = null;

    /**
     * ext_update constructor.
     */
    public function __construct()
    {
        $this->updateCookiesService = ObjectUtility::getObjectManager()->get(UpdateIdcookiesService::class);
    }

    /**
     * Main function, returning the HTML content
     *
     * @return string HTML
     * @throws DBALException
     */
    public function main()
    {
        try {
            $this->updateCookiesService->createNewRecordsFromOldRecords();
            return 'Update done!';
        } catch (\LogicException $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @return bool
     * @throws DBALException
     */
    public function access()
    {
        return $this->updateCookiesService->isUpdateAvailable();
    }
}

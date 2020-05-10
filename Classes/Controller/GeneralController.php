<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class GeneralController
 */
class GeneralController extends AbstractController
{
    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    public function informationAction(): void
    {
        $filter = ObjectUtility::getFilterDto(FilterDto::PERIOD_THISYEAR);
        $values = [
            'statistics' => [
                'visitors' => $this->visitorRepository->findAllAmount(),
                'identified' => $this->visitorRepository->findAllIdentifiedAmount(),
                'unknown' => $this->visitorRepository->findAllUnknownAmount(),
                'identifiedEmail4Link' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_EMAIL4LINK, $filter),
                'identifiedFieldListening' => $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED, $filter),
                'identifiedFormListening' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_FORMLISTENING, $filter),
                'identifiedFrontendLogin' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION, $filter),
                'identifiedLuxletter' =>
                    $this->logRepository->findByStatusAmount(Log::STATUS_IDENTIFIED_LUXLETTERLINK, $filter),
                'luxcategories' => $this->categoryRepository->findAllAmount(),
                'pagevisits' => $this->pagevisitsRepository->findAllAmount(),
                'downloads' => $this->downloadRepository->findAllAmount(),
                'versionLux' => ExtensionUtility::getLuxVersion(),
                'versionLuxenterprise' => ExtensionUtility::getLuxenterpriseVersion(),
                'versionLuxletter' => ExtensionUtility::getLuxletterVersion(),
                'linkclicks' => $this->linkclickRepository->findAllAmount(),
                'fingerprints' => $this->fingerprintRepository->findAllAmount(),
                'ipinformations' => $this->ipinformationRepository->findAllAmount(),
                'logs' => $this->logRepository->findAllAmount()
            ]
        ];
        $this->view->assignMultiple($values);
    }
}

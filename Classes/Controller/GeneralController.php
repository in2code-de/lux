<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class GeneralController
 * to show general information by clicking on the help icon
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

    /**
     * Save if pageOverview layout should be shown or hidden
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function showOrHidePageOverviewAjax(ServerRequestInterface $request): ResponseInterface
    {
        BackendUtility::saveValueToSession('toggle', 'PageOverview', 'General', $request->getQueryParams());
        return ObjectUtility::getObjectManager()->get(JsonResponse::class);
    }
}

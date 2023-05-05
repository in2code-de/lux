<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\ExtensionUtility;
use In2code\Lux\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeneralController extends AbstractController
{
    public function informationAction(): ResponseInterface
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
                'logs' => $this->logRepository->findAllAmount(),
            ],
        ];
        $this->view->assignMultiple($values);

        $this->addNavigationButtons([]);
        return $this->defaultRendering();
    }

    /**
     * Save if pageOverview layout should be shown or hidden
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function showOrHidePageOverviewAjax(ServerRequestInterface $request): ResponseInterface
    {
        BackendUtility::saveValueToSession('toggle', 'PageOverview', 'General', $request->getQueryParams());
        return GeneralUtility::makeInstance(JsonResponse::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getVisitorImageUrlAjax(ServerRequestInterface $request): ResponseInterface
    {
        $visitorIdentifier = $request->getQueryParams()['visitor'] ?? 0;
        /** @var Visitor $visitor */
        $visitor = $this->visitorRepository->findByUid($visitorIdentifier);
        $url = '';
        if ($visitor !== null) {
            $url = $visitor->getImageUrl();
        }
        return GeneralUtility::makeInstance(JsonResponse::class, ['url' => $url]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getLinkListenerPerformanceAjax(ServerRequestInterface $request): ResponseInterface
    {
        $linkListenerIdentifier = $request->getQueryParams()['linkListener'] ?? 0;
        /** @var Linklistener $linkListener */
        $linkListener = $this->linklistenerRepository->findByUid($linkListenerIdentifier);
        $performance = '';
        if ($linkListener !== null) {
            $performance = $linkListener->getPerformance();
        }
        return GeneralUtility::makeInstance(JsonResponse::class, ['performance' => $performance]);
    }
}

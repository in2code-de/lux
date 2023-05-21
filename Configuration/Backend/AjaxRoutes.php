<?php

use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Controller\GeneralController;
use In2code\Lux\Controller\LeadController;

return [
    '/lux/analysiscontentdetaildownload' => [
        'path' => '/lux/analysiscontentdetaildownload',
        'target' => AnalysisController::class . '::detailAjaxDownload',
    ],
    '/lux/analysiscontentdetailpage' => [
        'path' => '/lux/analysiscontentdetailpage',
        'target' => AnalysisController::class . '::detailAjaxPage',
    ],
    '/lux/analysislinklistenerdetail' => [
        'path' => '/lux/analysislinklistenerdetail',
        'target' => AnalysisController::class . '::detailAjaxLinklistener',
    ],
    '/lux/analysisnewsdetailpage' => [
        'path' => '/lux/analysisnewsdetailpage',
        'target' => AnalysisController::class . '::detailNewsAjaxPage',
    ],
    '/lux/analysissearchdetailpage' => [
        'path' => '/lux/analysissearchdetailpage',
        'target' => AnalysisController::class . '::detailSearchAjaxPage',
    ],
    '/lux/analysisutmdetailpage' => [
        'path' => '/lux/analysisutmdetailpage',
        'target' => AnalysisController::class . '::detailUtmAjaxPage',
    ],
    '/lux/companiesinformation' => [
        'path' => '/lux/companiesinformation',
        'target' => LeadController::class . '::companiesInformationAjax',
    ],
    '/lux/leadlistdetail' => [
        'path' => '/lux/leadlistdetail',
        'target' => LeadController::class . '::detailAjax',
    ],
    '/lux/linklistenerperformance' => [
        'path' => '/lux/linklistenerperformance',
        'target' => GeneralController::class . '::getLinkListenerPerformanceAjax',
    ],
    '/lux/pageoverview' => [
        'path' => '/lux/pageoverview',
        'target' => GeneralController::class . '::showOrHidePageOverviewAjax',
    ],
    '/lux/visitordescription' => [
        'path' => '/lux/visitordescription',
        'target' => LeadController::class . '::detailDescriptionAjax',
    ],
    '/lux/visitorimage' => [
        'path' => '/lux/visitorimage',
        'target' => GeneralController::class . '::getVisitorImageUrlAjax',
    ],
];

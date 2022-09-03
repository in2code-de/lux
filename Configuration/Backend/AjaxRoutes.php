<?php

use In2code\Lux\Controller\AnalysisController;
use In2code\Lux\Controller\GeneralController;
use In2code\Lux\Controller\LeadController;

return [
    '/lux/leadlistdetail' => [
        'path' => '/lux/leadlistdetail',
        'target' => LeadController::class . '::detailAjax',
    ],
    '/lux/analysiscontentdetailpage' => [
        'path' => '/lux/analysiscontentdetailpage',
        'target' => AnalysisController::class . '::detailAjaxPage',
    ],
    '/lux/analysisnewsdetailpage' => [
        'path' => '/lux/analysisnewsdetailpage',
        'target' => AnalysisController::class . '::detailNewsAjaxPage',
    ],
    '/lux/analysissearchdetailpage' => [
        'path' => '/lux/analysissearchdetailpage',
        'target' => AnalysisController::class . '::detailSearchAjaxPage',
    ],
    '/lux/analysiscontentdetaildownload' => [
        'path' => '/lux/analysiscontentdetaildownload',
        'target' => AnalysisController::class . '::detailAjaxDownload',
    ],
    '/lux/analysislinklistenerdetail' => [
        'path' => '/lux/analysislinklistenerdetail',
        'target' => AnalysisController::class . '::detailAjaxLinklistener',
    ],
    '/lux/visitordescription' => [
        'path' => '/lux/visitordescription',
        'target' => LeadController::class . '::detailDescriptionAjax',
    ],
    '/lux/visitorimage' => [
        'path' => '/lux/visitorimage',
        'target' => GeneralController::class . '::getVisitorImageUrlAjax',
    ],
    '/lux/pageoverview' => [
        'path' => '/lux/pageoverview',
        'target' => GeneralController::class . '::showOrHidePageOverviewAjax',
    ],
];

<?php
return [
    '/lux/leadlistdetail' => [
        'path' => '/lux/leadlistdetail',
        'target' => \In2code\Lux\Controller\LeadController::class . '::detailAjax',
    ],
    '/lux/analysiscontentdetailpage' => [
        'path' => '/lux/analysiscontentdetailpage',
        'target' => \In2code\Lux\Controller\AnalysisController::class . '::detailAjaxPage',
    ],
    '/lux/analysiscontentdetaildownload' => [
        'path' => '/lux/analysiscontentdetaildownload',
        'target' => \In2code\Lux\Controller\AnalysisController::class . '::detailAjaxDownload',
    ],
    '/lux/analysislinklistenerdetail' => [
        'path' => '/lux/analysislinklistenerdetail',
        'target' => \In2code\Lux\Controller\AnalysisController::class . '::detailAjaxLinklistener',
    ],
    '/lux/visitordescription' => [
        'path' => '/lux/visitordescription',
        'target' => \In2code\Lux\Controller\LeadController::class . '::detailDescriptionAjax',
    ]
];

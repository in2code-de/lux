<?php
return [
    '/lux/detail' => [
        'path' => '/lux/detail',
        'target' => \In2code\Lux\Controller\LeadController::class . '::detailAjax',
    ],
    '/lux/visitordescription' => [
        'path' => '/lux/visitordescription',
        'target' => \In2code\Lux\Controller\LeadController::class . '::detailDescriptionAjax',
    ]
];

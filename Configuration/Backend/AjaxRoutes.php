<?php
return [
    '/lux/detail' => [
        'path' => '/lux/detail',
        'target' => \In2code\Lux\Controller\LeadController::class . '::detailAjax',
    ],
    '/lux/visitordescription' => [
        'path' => '/lux/visitordescription',
        'target' => \In2code\Lux\Controller\LeadController::class . '::detailDescriptionAjax',
    ],
    '/lux/addtrigger' => [
        'path' => '/lux/addtrigger',
        'target' => \In2code\Lux\Controller\WorkflowController::class . '::addTriggerAjax',
    ],
    '/lux/addaction' => [
        'path' => '/lux/addaction',
        'target' => \In2code\Lux\Controller\WorkflowController::class . '::addActionAjax',
    ]
];

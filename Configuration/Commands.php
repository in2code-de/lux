<?php
declare(strict_types = 1);

use In2code\Lux\Command\LuxAnonymizeCommand;
use In2code\Lux\Command\LuxAutorelateToFrontendUsersCommand;
use In2code\Lux\Command\LuxCacheWarmupCommand;
use In2code\Lux\Command\LuxCleanupAllVisitorsCommand;
use In2code\Lux\Command\LuxCleanupUnknownVisitorsByAgeCommand;
use In2code\Lux\Command\LuxCleanupVisitorByUidCommand;
use In2code\Lux\Command\LuxCleanupVisitorsByAgeCommand;
use In2code\Lux\Command\LuxCleanupVisitorsByPropertyCommand;
use In2code\Lux\Command\LuxLeadSendSummaryCommand;
use In2code\Lux\Command\LuxLeadSendSummaryOfKnownCompaniesCommand;
use In2code\Lux\Command\LuxLeadSendSummaryOfLuxCategoryCommand;
use In2code\Lux\Command\LuxServiceRecalculateScoringCommand;

return [
    'lux:anonymize' => [
        'class' => LuxAnonymizeCommand::class,
        'schedulable' => false
    ],
    'lux:cachewarmup' => [
        'class' => LuxCacheWarmupCommand::class,
        'schedulable' => false
    ],
    'lux:cleanupUnknownVisitorsByAge' => [
        'class' => LuxCleanupUnknownVisitorsByAgeCommand::class,
        'schedulable' => true
    ],
    'lux:cleanupVisitorsByAge' => [
        'class' => LuxCleanupVisitorsByAgeCommand::class,
        'schedulable' => true
    ],
    'lux:cleanupAllVisitors' => [
        'class' => LuxCleanupAllVisitorsCommand::class,
        'schedulable' => true
    ],
    'lux:cleanupVisitorByUid' => [
        'class' => LuxCleanupVisitorByUidCommand::class,
        'schedulable' => true
    ],
    'lux:cleanupVisitorsByProperty' => [
        'class' => LuxCleanupVisitorsByPropertyCommand::class,
        'schedulable' => true
    ],
    'lux:serviceRecalculateScoring' => [
        'class' => LuxServiceRecalculateScoringCommand::class,
        'schedulable' => true
    ],
    'lux:autorelateToFrontendUsers' => [
        'class' => LuxAutorelateToFrontendUsersCommand::class,
        'schedulable' => true
    ],
    'lux:leadSendSummary' => [
        'class' => LuxLeadSendSummaryCommand::class,
        'schedulable' => true
    ],
    'lux:leadSendSummaryOfLuxCategory' => [
        'class' => LuxLeadSendSummaryOfLuxCategoryCommand::class,
        'schedulable' => true
    ],
    'lux:leadSendSummaryOfKnownCompaniesCommand' => [
        'class' => LuxLeadSendSummaryOfKnownCompaniesCommand::class,
        'schedulable' => true
    ],
    'lux:cachewarmup' => [
        'class' => LuxCacheWarmupCommand::class,
        'schedulable' => true
    ],
];

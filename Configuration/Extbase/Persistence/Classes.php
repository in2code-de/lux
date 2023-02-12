<?php

declare(strict_types=1);

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Model\File;
use In2code\Lux\Domain\Model\FrontendUser;
use In2code\Lux\Domain\Model\FrontendUserGroup;
use In2code\Lux\Domain\Model\Metadata;
use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\User;

return [
    FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    FrontendUserGroup::class => [
        'tableName' => 'fe_groups',
    ],
    Page::class => [
        'tableName' => 'pages',
    ],
    User::class => [
        'tableName' => 'be_users',
    ],
    Category::class => [
        'tableName' => 'sys_category',
    ],
    File::class => [
        'tableName' => 'sys_file',
    ],
    Metadata::class => [
        'tableName' => 'sys_file_metadata',
    ],
    News::class => [
        'tableName' => 'tx_news_domain_model_news',
    ],
];

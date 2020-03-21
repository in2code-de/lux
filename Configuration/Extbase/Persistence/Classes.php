<?php
declare(strict_types = 1);

return [
    \In2code\Lux\Domain\Model\Page::class => [
        'tableName' => 'pages'
    ],
    \In2code\Lux\Domain\Model\User::class => [
        'tableName' => 'be_users'
    ],
    \In2code\Lux\Domain\Model\Category::class => [
        'tableName' => 'sys_category'
    ],
    \In2code\Lux\Domain\Model\File::class => [
        'tableName' => 'sys_file'
    ],
    \In2code\Lux\Domain\Model\Metadata::class => [
        'tableName' => 'sys_file_metadata'
    ]
];

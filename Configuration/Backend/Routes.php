<?php

use Visol\CcSetsearch\Controller\SetPageController;

return [
    'pages_set_search' => [
        'path' => '/pages/set-search',
        'target' => SetPageController::class . '::mainAction',
        'redirect' => [
            'enable' => true,
            'parameters' => [
                'id' => true,
            ],
        ],
    ],
];

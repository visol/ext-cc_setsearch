<?php


use Visol\CcSetsearch\Controller\SetSearchController;

return [
    'pages_set_search' => [
        'path' => '/pages/set-search',
        'target' => SetSearchController::class . '::mainAction',
        'redirect' => [
            'enable' => true,
            'parameters' => [
                'id' => true,
            ],
        ],
    ],
];

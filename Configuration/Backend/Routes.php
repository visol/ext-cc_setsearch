<?php


use Visol\CcSetsearch\Controller\SetSearchWizardController;

return [
    'pages_set_search' => [
        'path' => '/pages/set-search',
        'target' => SetSearchWizardController::class . '::mainAction',
        'redirect' => [
            'enable' => true,
            'parameters' => [
                'id' => true,
            ],
        ],
    ],
];

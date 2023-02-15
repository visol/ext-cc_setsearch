<?php

use Visol\CcSetsearch\Controller\SetPageAjaxController;

return [
    'pages_set_search_ajax' => [
        'path' => '/pages/set-search-ajax',
        'target' => SetPageAjaxController::class . '::mainAction',
    ],
];

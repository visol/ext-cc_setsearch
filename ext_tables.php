<?php

use Visol\CcSetsearch\ContextMenu\SetSearchItemProvider;

if (!defined('TYPO3')) {
    die ("Access denied.");
}

$GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders'][1668611778] = SetSearchItemProvider::class;

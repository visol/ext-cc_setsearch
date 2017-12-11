<?php
if (!defined("TYPO3_MODE")) {
    die ("Access denied.");
}

if (TYPO3_MODE == "BE") {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
        "web_func",
        \Visol\CcSetsearch\Controller\SetsearchWizardModuleFunctionController::class,
        null,
        "LLL:EXT:cc_setsearch/Resources/Private/Language/locallang.xlf:title"
    );
}

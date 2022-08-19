<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Visol\CcSetsearch\Controller\SetsearchWizardModuleFunctionController;
if (!defined('TYPO3')) {
    die ("Access denied.");
}

ExtensionManagementUtility::insertModuleFunction(
    "web_func",
    SetsearchWizardModuleFunctionController::class,
    null,
    "LLL:EXT:cc_setsearch/Resources/Private/Language/locallang.xlf:title"
);

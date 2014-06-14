<?php
if (!defined("TYPO3_MODE")) die ("Access denied.");

if (TYPO3_MODE == "BE") {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		"web_func",
		"tx_ccsetsearch_modfunc1",
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . "modfunc1/class.tx_ccsetsearch_modfunc1.php",
		"LLL:EXT:cc_setsearch/locallang_db.xml:moduleFunction.tx_ccsetsearch_modfunc1",
		"wiz"
	);
}

?>
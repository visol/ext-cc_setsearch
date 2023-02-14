<?php

namespace Visol\CcSetsearch\Traits;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait ExtensionConfigurationTrait {

    protected function getExtensionConfiguration(string $key): array
    {
        $configurations = GeneralUtility::makeInstance(
            ExtensionConfiguration::class
        )->get('cc_setsearch');

        return GeneralUtility::trimExplode(',', $configurations[$key]);
    }
}

<?php

namespace Visol\CcSetsearch\Traits;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait BackendRecordTrait {

    private int $permissionEditPage = 2;

    protected function checkPermissionsForRow($row): bool
    {
        if ($this->getBackendUser()->isAdmin()) {
            return true;
        }

        if ($this->getBackendUser()->doesUserHaveAccess($row, $this->permissionEditPage)) {
            return true;
        }

        return false;
    }

    protected function update(array $uids, string $field, int $value)
    {
        $data = [];
        foreach ($uids as $uid) {
            $data['pages'][$uid][$field] = $value;
        }

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}

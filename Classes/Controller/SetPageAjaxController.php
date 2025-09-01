<?php

namespace Visol\CcSetsearch\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\JsonResponse;
use Visol\CcSetsearch\Traits\BackendRecordTrait;
use Visol\CcSetsearch\Traits\ExtensionConfigurationTrait;

class SetPageAjaxController
{
    use BackendRecordTrait;
    use ExtensionConfigurationTrait;

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $nextValue = null;
        $field = $request->getQueryParams()['field'] ?? '';
        if (in_array($field, $this->getExtensionConfiguration('fields'), true)) {
            $uid = (int) $request->getQueryParams()['uid'];
            $page = BackendUtility::getRecord('pages', $uid);
            if ($this->checkPermissionsForRow($page)) {
                $nextValue = (int) !(bool)$page[$field];
                $this->update([$uid], $field, $nextValue);
            }
        }
        return new JsonResponse(['result' => $nextValue]);
    }
}

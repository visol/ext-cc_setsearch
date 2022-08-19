<?php

namespace Visol\CcSetsearch\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004-2005 René Fritz (r.fritz@colorcube.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Module extension (addition to function menu) 'Set index flag (recursive)' for the 'cc_setsearch' extension.
 *
 * @author    René Fritz <r.fritz@colorcube.de>
 */
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Creates the "set searchable" wizard
 *
 * @author    René Fritz <r.fritz@colorcube.de>
 */
class SetsearchWizardModuleFunctionController extends AbstractFunctionModule
{

    const PERMISSION_EDIT_PAGE = 2;

    /**
     * @return string
     */
    public function main(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:cc_setsearch/Resources/Private/Templates/Index.html'
        ));

        $id = $this->pObj->id;
        $depth = GeneralUtility::_GP('depth') ?: 3;
        $cmd = GeneralUtility::_GP('cmd');

        switch ($cmd) {
            case 'setsearchable':
                $uids = $this->getRecursivePageUids($id, $depth);
                $this->setNoSearchValue($uids, 0);
                break;
            case 'setnonsearchable':
                $uids = $this->getRecursivePageUids($id, $depth);
                $this->setNoSearchValue($uids, 1);
                break;
        }

        $view->assign('depth', $depth);

        $depthBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_func', [
            'SET' => [
                'function' => self::class,
            ],
            'id' => $id,
            'depth' => '__DEPTH__',
        ]);
        $view->assign('depthBaseUrl', $depthBaseUrl);

        $idBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_func', [
            'SET' => [
                'function' => self::class,
            ],
            'depth' => $depth,
        ]);
        $view->assign('idBaseUrl', $idBaseUrl);

        $cmdBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_func', [
            'SET' => [
                'function' => self::class,
            ],
            'id' => $id,
            'depth' => $depth,
        ]);
        $view->assign('cmdBaseUrl', $cmdBaseUrl);

        $depthOptions = [];
        foreach ([1, 2, 3, 4, 10] as $depthLevel) {
            $levelLabel = $depthLevel === 1 ? 'level' : 'levels';
            $depthOptions[$depthLevel] = $depthLevel . ' ' . LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang_mod_permission.xlf:' . $levelLabel,
                    'beuser');
        }
        $view->assign('depthOptions', $depthOptions);

        $view->assign('LLPrefix', 'LLL:EXT:cc_setsearch/Resources/Private/Language/locallang.xlf:');

        $tree = $this->getPageTree($id, $depth);
        $view->assign('viewTree', $tree->tree);

        return $view->render();
    }

    /**
     * Return an array of page id's where the user have access to
     *
     * @param $id
     * @param $depth
     *
     * @return array
     */
    protected function getRecursivePageUids($id, $depth): array
    {
        $tree = $this->getPageTree($id, $depth);

        $uidList = [];

        if ($this->getBackendUser()->user['uid'] && count($tree->tree) > 0) {
            foreach ($tree->tree as $item) {
                if ($this->checkPermissionsForRow($item['row'])) {
                    $uidList[] = $item['row']['uid'];
                }
            }
        }

        return $uidList;

    }

    /**
     * Reads the page tree
     *
     * @return PageTreeView
     */
    protected function getPageTree($id, $depth): PageTreeView
    {
        /** @var PageTreeView $tree */
        $tree = GeneralUtility::makeInstance(PageTreeView::class);
        $tree->init(' AND ' . $this->pObj->perms_clause);
        $tree->addField('no_search');
        $tree->addField('perms_userid');
        $tree->addField('perms_groupid');
        $tree->addField('perms_user');
        $tree->addField('perms_group');
        $tree->addField('perms_everybody');

        if ($id) {
            $pageInfo = BackendUtility::readPageAccess($id, ' 1=1');
            $tree->tree[] = ['row' => $pageInfo, 'HTML' => $tree->getIcon($id)];
        } else {
            $pageInfo = ['title' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'], 'uid' => 0, 'pid' => 0];
            $tree->tree[] = ['row' => $pageInfo, 'HTML' => $tree->getRootIcon($pageInfo)];
        }

        $tree->getTree($id, $depth, '');

        return $tree;
    }

    protected function checkPermissionsForRow($row): bool
    {
        if ($this->getBackendUser()->isAdmin()) {
            return true;
        }

        if ($this->getBackendUser()->doesUserHaveAccess($row, self::PERMISSION_EDIT_PAGE)) {
            return true;
        }

        return false;
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @param array $uids
     * @param $value
     */
    protected function setNoSearchValue(array $uids, $value)
    {
        $data = [];
        foreach ($uids as $uid) {
            $data['pages'][$uid]['no_search'] = $value;
        }

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
    }
}

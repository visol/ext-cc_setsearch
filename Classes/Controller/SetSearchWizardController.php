<?php

namespace Visol\CcSetsearch\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class SetSearchWizardController
{
    const PERMISSION_EDIT_PAGE = 2;

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    protected IconFactory $iconFactory;
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    }

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:cc_setsearch/Resources/Private/Templates/Index.html'
        ));

        $id = (int)$request->getQueryParams()['id'];
        $depth = (int)GeneralUtility::_GP('depth') ?: 3;
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

        $depthBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_set_search', [
            'SET' => [
                'function' => self::class,
            ],
            'id' => $id,
            'depth' => '__DEPTH__',
        ]);
        $view->assign('depthBaseUrl', $depthBaseUrl);

        $idBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_set_search', [
            'SET' => [
                'function' => self::class,
            ],
            'depth' => $depth,
        ]);
        $view->assign('idBaseUrl', $idBaseUrl);

        $cmdBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_set_search', [
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

        $this->moduleTemplate->setContent($view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Return an array of page id's where the user have access to
     */
    protected function getRecursivePageUids(int $id, int $depth): array
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
     */
    protected function getPageTree(int $id, int $depth): PageTreeView
    {
        /** @var PageTreeView $tree */
        $tree = GeneralUtility::makeInstance(PageTreeView::class);
        $tree->init(' AND ' . $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW));
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

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function setNoSearchValue(array $uids, int $value)
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

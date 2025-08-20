<?php

namespace Visol\CcSetsearch\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Visol\CcSetsearch\Traits\BackendRecordTrait;
use Visol\CcSetsearch\Traits\ExtensionConfigurationTrait;

class SetPageController
{
    use ExtensionConfigurationTrait;
    use BackendRecordTrait;

    protected ModuleTemplate $moduleTemplate;

    protected IconFactory $iconFactory;

    protected ModuleTemplateFactory $moduleTemplateFactory;

    protected PageRenderer $pageRenderer;

    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
    }

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->pageRenderer->loadJavaScriptModule('@visol/CcSetsearch/SetPageAjaxActions');

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $id = (int)$request->getQueryParams()['id'];
        $depth = $request->getQueryParams()['depth'] ? (int)$request->getQueryParams()['depth'] : 3;
        $cmd = $request->getQueryParams()['cmd'];
        $field = $request->getQueryParams()['field'];

        // We update the records if required
        if (in_array($field, $this->getExtensionConfiguration('fields'), true) &&
            in_array($cmd, ['set', 'unset'])) {
            $ids = $this->getRecursivePageUids($id, $depth);
            $this->update($ids, $field, (int)($cmd === 'unset'));
        }

        $moduleTemplate->assign('depthBaseUrl', $this->generateUrl(['id' => $id, 'depth' => '__DEPTH__',]));
        $moduleTemplate->assign('idBaseUrl', $this->generateUrl(['depth' => $depth,]));
        $moduleTemplate->assign('cmdBaseUrl', $this->generateUrl(['id' => $id, 'depth' => $depth,]));

        $depthOptions = [];
        foreach ([1, 2, 3, 4, 10] as $depthLevel) {
            $levelLabel = $depthLevel === 1 ? 'level' : 'levels';
            $depthOptions[$depthLevel] = $depthLevel . ' ' . LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang_mod_permission.xlf:' . $levelLabel,
                    'beuser');
        }

        $moduleTemplate->assignMultiple([
            'depth' => $depth,
            'depthOptions' => $depthOptions,
            'LLPrefix' => 'LLL:EXT:cc_setsearch/Resources/Private/Language/locallang.xlf:',
            'viewTree' => $this->getPageTree($id, $depth)->tree,
            'fields' => $this->getConfiguredFields(),
        ]);

        return $moduleTemplate->renderResponse('Index');
    }

    /**
     * Just rework the fields array for convenience’s sake.
     */
    protected function getConfiguredFields(): array
    {
        $fields = $this->getExtensionConfiguration('fields');
        $labels = $this->getExtensionConfiguration('labels');

        $configuredFields = [];
        foreach ($fields as $key => $field) {
            $configuredFields[] = [
                'name' => $field,
                'label' => $labels[$key],
            ];
        }

        return $configuredFields;
    }

    protected function generateUrl(array $config): string
    {
        $mergedConfiguration = array_merge(
            [
                'SET' => [
                    'function' => self::class,
                ],
            ],
            $config
        );
        return GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_set_search', $mergedConfiguration);
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
        foreach ($this->getExtensionConfiguration('fields') as $fieldName) {
            $tree->addField($fieldName);
        }
        $tree->addField('perms_userid');
        $tree->addField('perms_groupid');
        $tree->addField('perms_user');
        $tree->addField('perms_group');
        $tree->addField('perms_everybody');

        if ($id !== 0) {
            $pageInfo = BackendUtility::readPageAccess($id, ' 1=1');
            //$tree->tree[] = ['row' => $pageInfo, 'HTML' => $tree->getIcon($id)];
        } else {
            $pageInfo = ['title' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'], 'uid' => 0, 'pid' => 0];
            //  $tree->tree[] = ['row' => $pageInfo, 'HTML' => $tree->getRootIcon($pageInfo)];
        }

        $tree->getTree($id, $depth, '');

        return $tree;
    }

}

<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Ren� Fritz (r.fritz@colorcube.de)
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
 * @author	Ren� Fritz <r.fritz@colorcube.de>
 */

/**
 * Creates the "set searchable" wizard
 * 
 * @author	Ren� Fritz <r.fritz@colorcube.de>
 */
class tx_ccsetsearch_modfunc1 extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule {

	var $tree;

	/**
	 * Adds menu items
	 * 
	 * @return	array		
	 * @ignore
	 */	
	function modMenu()	{
		$levelsLabel = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_perm.xlf:levels');

		return array(
			'tx_ccsetsearch_modfunc1_depth' => array(
				1 => '1 '.$levelsLabel,
				2 => '2 '.$levelsLabel,
				3 => '3 '.$levelsLabel,
				4 => '4 '.$levelsLabel,
				10 => '10 '.$levelsLabel
			)
		);
	}

	/**
	 * Main function creating the content for the module.
	 * 
	 * @return	string		HTML content for the module, actually a "section" made through the parent object in $this->pObj
	 */
	function main()	{
		$GLOBALS['LANG']->includeLLFile('EXT:cc_setsearch/locallang.xml');
		define('TYPO3_MOD_PATH', 'sysext/func/mod1/');

		$this->getPageTree();

			// title
		$theOutput = $this->pObj->doc->spacer(5);
		$theOutput .= $this->pObj->doc->section($GLOBALS['LANG']->getLL('title'),'',0,1);

			// depth menu
		$menu=$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_perm.php:Depth').': '.
			\TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->pObj->id,
				'SET[tx_ccsetsearch_modfunc1_depth]',
				$this->pObj->MOD_SETTINGS['tx_ccsetsearch_modfunc1_depth'],
				$this->pObj->MOD_MENU['tx_ccsetsearch_modfunc1_depth']
			);
		$theOutput .= $this->pObj->doc->spacer(5);
		$theOutput .= $this->pObj->doc->section('',$menu,0,1);

			// output page tree
		$theOutput .= $this->pObj->doc->spacer(10);
		$theOutput .= $this->pObj->doc->section('',$this->showPageTree(),0,1);

			// new form (close old)
		$theOutput .= '</form>';
		$theOutput .= $this->pObj->doc->spacer(10);

		$theOutput .= '<form action="'.$GLOBALS['BACK_PATH'].'tce_db.php" method="POST" name="editform">';
		$theOutput .= '<input type="hidden" name="id" value="'.$this->pObj->id.'">';
		//$theOutput .= '<input type="hidden" name="redirect" value="' . TYPO3_MOD_PATH . 'index.php?id='.$this->pObj->id.'">';
		$theOutput .= '<input type="hidden" name="redirect" value="' . \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('web_func') . '&id='.$this->pObj->id.'">';

		$theOutput .= \TYPO3\CMS\Backend\Form\FormEngine::getHiddenTokenField('tceAction');

		$theOutput .= '<input type="hidden" name="data[pages]['.$this->pObj->id.'][no_search]" value="1">';
		$theOutput .= '<input type="hidden" name="mirror[pages]['.$this->pObj->id.']" value="'.htmlspecialchars(implode(',',$this->getRecursivePageIDArray())).'">';

			// submit buttons
		$theOutput .= '<input type="submit" name="setSearchable" value="'.$GLOBALS['LANG']->getLL('setSearchable').'" onclick="document.editform[\'data[pages]['.$this->pObj->id.'][no_search]\'].value=0;"> ';
		$theOutput .= '<input type="submit" name="setNonSearchable" value="'.$GLOBALS['LANG']->getLL('setNonSearchable').'">';


		return $theOutput;
	}


	function showPageTree()	{
		$tableLayout = array (
			'table' => array ('<table border="0" cellspacing="1" cellpadding="0" id="typo3-tree" style="width:auto;">', '</table>'),
			'0' => array (
				'tr' => array('<tr class="tableheader bgColor5">','</tr>'),
				'0' => array('<td colspan="2" align="right" nowrap="nowrap" class="bgColor5">','</td>'),
				'1' => array('',''),
			),
			'defRow' => array (
				'tr' => array('<tr class="bgColor4">','</tr>'),
				'0' => array('<td nowrap="nowrap">','</td>'),
				'1' => array('<td align="center" nowrap="nowrap">&nbsp;&nbsp;','&nbsp;&nbsp;</td>'),
			)
		);

		$table=array();
		$tr=0;
		$table[$tr++][0]='<strong>'.$GLOBALS['LANG']->getLL('searchable').':</strong>';

		foreach($this->tree->tree as $pageItem)	{
			if (!($this->admin || $GLOBALS['BE_USER']->doesUserHaveAccess($pageItem['row'],$perms)))	{
				$tableLayout[$tr]['tr'] = array('<tr class="bgColor4-20">','</tr>');
			}

			$title = \TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($this->tree->getTitleStr($pageItem['row']),$GLOBALS['BE_USER']->uc['titleLen']);
			$treeItem = $pageItem['HTML'].$this->tree->wrapTitle($title,$pageItem['row']);

			if ($pageItem['row']['no_search']) {
				$searchFlag = '<span style="color:red">&times;</span>';
			} else {
				$searchFlag = '<span style="color:green">&bull;</span>';
			}

 			$table[$tr][0] = $treeItem.'&nbsp;';
			$table[$tr++][1] = $searchFlag;
		}

		return $this->pObj->doc->table($table, $tableLayout);
	}


	/**
	 * Reads the page tree
	 * 
	 * @return	void
	 */
	function getPageTree()	{
		$this->tree = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_ccsetsearch_pageTree');
		$this->tree->init(' AND '.$this->pObj->perms_clause);
		$this->tree->setRecs = 1;
		$this->tree->makeHTML = true;
		$this->tree->thisScript = 'index.php';
		$this->tree->addField('no_search');
		$this->tree->addField('perms_userid',1);
		$this->tree->addField('perms_groupid',1);
		$this->tree->addField('perms_user',1);
		$this->tree->addField('perms_group',1);
		$this->tree->addField('perms_everybody',1);

			// set Root icon
		$HTML='<img src="'.$GLOBALS['BACK_PATH'] . \TYPO3\CMS\Backend\Utility\IconUtility::getIcon('pages',$this->pObj->pageinfo).'" title="' . \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordIconAltText($this->pObj->pageinfo, $this->tree->table).'" width="18" height="16" align="top">';
		$this->tree->tree[]=Array('row'=>$this->pObj->pageinfo, 'HTML'=>$HTML);

		$this->tree->getTree($this->pObj->id, $this->pObj->MOD_SETTINGS['tx_ccsetsearch_modfunc1_depth'],'');
	}


	/**
	 * Return an array of page id's where the user have access to
	 * 
	 * @return	array	pages uid array
	 */	
	function getRecursivePageIDArray()	{
		$theIdListArr=array();

		if ($GLOBALS['BE_USER']->user['uid'] && count($this->tree->ids_hierarchy))	{
			reset($this->tree->ids_hierarchy);
			$theIdListArr=array();
			for ($a=$this->pObj->MOD_SETTINGS['tx_ccsetsearch_modfunc1_depth']; $a>0; $a--)	{
				if (is_array($this->tree->ids_hierarchy[$a]))	{
					reset($this->tree->ids_hierarchy[$a]);
					while(list(,$theId)=each($this->tree->ids_hierarchy[$a]))	{
						if ($this->admin || $GLOBALS['BE_USER']->doesUserHaveAccess($this->tree->tree[$theId]['row'],$perms))	{
							$theIdListArr[]=$theId;
						}
					}
					$lKey = $getLevels-$a+1;
				}
			}
		}

		return $theIdListArr;
	}
}

/**
 * local version of the page tree
 * 
 * @author	René Fritz <r.fritz@colorcube.de>
 */
class tx_ccsetsearch_pageTree extends \TYPO3\CMS\Backend\Tree\View\PageTreeView {

	/**
	 * Wrapping $title in a-tags.
	 *
	 * @param	string		Title string
	 * @param	string		Item record
	 * @param	integer		Bank pointer (which mount point number)
	 * @return	string
	 * @access private
	 */
	function wrapTitle($title,$v)	{
		$aOnClick = 'return jumpToUrl(\'index.php?id='.$v['uid'].'\',this);';
		return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.$title.'</a>';
	}

	/**
	 * Creates title attribute content for pages.
	 * Uses API function in BackendUtility which will retrieve lots of useful information for pages.
	 *
	 * @param	array		The table row.
	 * @return	string
	 */
	function getTitleAttrib($row) {
		return $iconAltText = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordIconAltText($row, $this->table);
	}

	/**
	 * Wrapping the image tag, $icon, for the row, $row (except for mount points)
	 *
	 * @param	string		The image tag for the icon
	 * @param	array		The row for the current element
	 * @return	string		The processed icon input value.
	 * @access private
	 */
	function wrapIcon($icon,$row)	{
			// Add title attribute to input icon tag
		$theIcon = $this->addTagAttributes($icon,($this->titleAttrib ? $this->titleAttrib.'="'.$this->getTitleAttrib($row).'"' : ''));

		return $theIcon;
	}
}

?>

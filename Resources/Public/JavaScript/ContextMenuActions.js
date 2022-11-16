/**
 * Module: TYPO3/CMS/CcSetsearch/ContextMenuActions
 *
 * @exports TYPO3/CMS/CcSetsearch/ContextMenuActions
 */
define(['jquery'], function ($) {
  'use strict';

  /**
   * @exports TYPO3/CMS/CcSetsearch/ContextMenuActions
   */
  const ContextMenuActions = {};

  /**
   * @param {string} table
   * @param {int} uid of the page
   */
  ContextMenuActions.pageSetSearch = function (table, uid) {
    if (table === 'pages') {
      const url = $(this).data('pages-new-multiple-url');
      top.TYPO3.Backend.ContentContainer.setUrl(url);
    }
  };

  return ContextMenuActions;
});

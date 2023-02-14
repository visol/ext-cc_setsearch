/**
 * Module: TYPO3/CMS/CcSetsearch/SetPageAjaxActions
 *
 * @exports TYPO3/CMS/CcSetsearch/ContextMenuActions
 */

define(['jquery', 'TYPO3/CMS/Core/Ajax/AjaxRequest'], function ($, AjaxRequest) {
  'use strict';

  /**
   * @exports TYPO3/CMS/CcSetsearch/ContextMenuActions
   */
  const SetPageAjaxActions = {};

    $('.change-permission').click(function(e) {
        e.preventDefault();
        const el = this;
        new AjaxRequest(TYPO3.settings.ajaxUrls['pages_set_search_ajax'])
            .withQueryArguments({uid: $(this).data('uid'), field: $(this).data('field')})
            .get()
            .then(async function (response) {
                const resolved = await response.resolve();
                $(el)
                    .removeClass(['fa-times', 'fa-check', 'text-success', 'text-danger'])

                if (resolved.result !== null) {
                    $(el)
                        .addClass(resolved.result ? ['fa-times', 'text-danger'] : ['fa-check', 'text-success']);
                }
            });
        return false
    })

  return SetPageAjaxActions;
});

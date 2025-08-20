// File: SetPageAjaxActions.js
import $ from 'jquery';
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

/**
 * @module @visol/CcSetsearch/SetPageAjaxActions
 */
const SetPageAjaxActions = {};

// Attach click event for elements with class `change-permission`
$('.change-permission').on('click', function (e) {
    e.preventDefault();
    const el = this;

    new AjaxRequest(TYPO3.settings.ajaxUrls['pages_set_search_ajax'])
        .withQueryArguments({
            uid: $(el).data('uid'),
            field: $(el).data('field'),
        })
        .get()
        .then(async function (response) {
            const resolved = await response.resolve();

            $(el).removeClass('fa-times fa-check text-success text-danger');

            if (resolved.result !== null) {
                $(el).addClass(
                    resolved.result
                        ? 'fa-times text-danger'
                        : 'fa-check text-success'
                );
            }
        });

    return false;
});

export default SetPageAjaxActions;

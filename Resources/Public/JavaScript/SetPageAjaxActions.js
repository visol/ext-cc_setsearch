// // File: SetPageAjaxActions.js
// import $ from 'jquery';
// import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
//
// /**
//  * @module @visol/CcSetsearch/SetPageAjaxActions
//  */
// const SetPageAjaxActions = {};
//
// // Attach click event for elements with class `change-permission`
// $('.change-permission').on('click', function (e) {
//     e.preventDefault();
//     const el = this;
//
//     new AjaxRequest(TYPO3.settings.ajaxUrls['pages_set_search_ajax'])
//         .withQueryArguments({
//             uid: $(el).data('uid'),
//             field: $(el).data('field'),
//         })
//         .get()
//         .then(async function (response) {
//             const resolved = await response.resolve();
//
//             $(el).removeClass('fa-times fa-check text-success text-danger');
//
//             if (resolved.result !== null) {
//                 $(el).addClass(
//                     resolved.result
//                         ? 'fa-times text-danger'
//                         : 'fa-check text-success'
//                 );
//             }
//         });
//
//     return false;
// });

// export default SetPageAjaxActions;

// File: SetPageAjaxActions.js
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

/**
 * @module @visol/CcSetsearch/SetPageAjaxActions
 */
const SetPageAjaxActions = (() => {
    const ajaxUrl = TYPO3.settings.ajaxUrls["pages_set_search_ajax"];



    const handleClick = async (e) => {
        const el = e.target.closest(".change-permission");
        if (!el) return;

        e.preventDefault();

        try {
            const response = await new AjaxRequest(ajaxUrl)
                .withQueryArguments({
                    uid: Number(el.dataset.uid),
                    field: el.dataset.field,
                })
                .get();

            const { result } = await response.resolve();

            el.classList.remove("fa-times", "fa-check", "text-success", "text-danger");

            if (result !== null) {
                if (result) {
                    el.classList.add("fa-times", "text-danger");
                } else {
                    el.classList.add("fa-check", "text-success");
                }
            }
        } catch (err) {
            // Optionally log or handle error state here
            // console.error(err);
        }

        return false;
    };

    const init = () => {
        document.addEventListener("click", handleClick, false);
    };

    return { init };
})();

export default SetPageAjaxActions;

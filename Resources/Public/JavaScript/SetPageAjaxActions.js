import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import Icons from '@typo3/backend/icons.js';

/**
 * @module @visol/cc-setsearch/SetPageAjaxActions
 */
const SetPageAjaxActions = (() => {

    const selectElement = document.getElementById('depth');
    const depthBaseUrl = selectElement.dataset.depthBaseUrl;

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

            el.classList.remove("text-success", "text-danger");
            if (result !== null) {
                if (result) {
                    el.classList.add("text-danger");
                    const iconElement = el.querySelector('.t3js-icon');
                    Icons.getIcon('actions-close', Icons.sizes.small).then((icon) => {
                        iconElement.outerHTML = icon;
                    });
                } else {
                    el.classList.add("text-success");
                    const iconElement = el.querySelector('.t3js-icon');
                    Icons.getIcon('actions-check', Icons.sizes.small).then((icon) => {
                        iconElement.outerHTML = icon;
                    });
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
        const select = document.getElementById("depth");
        select.addEventListener("change", (e) => {
            const value = e.target.value;
            const url = depthBaseUrl.replace("__DEPTH__", value);
            window.location.href = url;
            return false;
        });
    };

    init();
})();

export default SetPageAjaxActions;

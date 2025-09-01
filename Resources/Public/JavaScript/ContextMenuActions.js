import $ from 'jquery';

/**
 * Module: @visol/CcSetsearch/ContextMenuActions
 */
class ContextMenuActions {
    /**
     * @param {string} table
     * @param {number} uid
     * @param {array} dataAttributes
     */

    pageSetSearch(table, uid, dataAttributes) {
        if (table === 'pages') {
            top.TYPO3.Backend.ContentContainer.setUrl(dataAttributes.pagesCcSetsearch);
        }
    }
};

export default new ContextMenuActions();

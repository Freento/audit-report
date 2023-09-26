define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    let mixin = {
        _create: function () {
            this._super();
            $('.ui-tabs-panel').on('contentUpdated', function (event) {
                event.currentTarget.classList.add('report-loaded');
            });
            // For compatibility with Magento 2.3.7 title must be assigned after tabs creating
            $('a.tab-item-link').removeAttr('title');
            $('.long-operation').attr(
                'title',
                $t('Generating a report on this tab may take long time. Please be patient.')
            );
        },

        load: function (index, event) {
            if (!this.panels[index].classList.contains('report-loaded')) {
                this._super(index, event);
            }
        }
    }

    return function (target) {
        if ($('.freento-auditreport-report-container').length) {
            $.widget('ui.tabs', target, mixin);
        }
        return $.ui.tabs;
    }
});

define([
   'jquery',
   'Vipps_Login/js/model/full-screen-loader',
   'mage/storage',
   'uiComponent',
],function($, fullScreenLoader, storage, Component) {
    'use strict';

    return Component.extend({
        options: {
            selectHolder: 'vipps_address'
        },
        initialize: function () {
            this._super();
            this.selectHolderChange();
        },
        selectHolderChange: function () {
            var self = this;
            $('#' + this.options.selectHolder).change(function () {
                var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].text.split(' ');

            })
        }


    });
});
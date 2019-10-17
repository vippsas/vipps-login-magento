/*
 * Copyright 2019 Vipps
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE
 */

/**
 * @api
 */
define([
   'jquery',
   'Magento_Ui/js/modal/modal',
   'Magento_Customer/js/customer-data'
], function ($, modal, storage) {
    'use strict';

    var cacheKey = 'vipps_login_data',

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = {
                    'addressUpdated': false
                };
                saveData(data);
            }

            return data;
        },

        options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttons: [
                {
                    text: $.mage.__('Cancel'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Only this time'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Update automatically'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }
            ]
        };

    var popup = modal(options, $('#popup-mpdal'));

    if (getData().addressUpdated) {
        $("#popup-mpdal").modal("openModal");
    }
});

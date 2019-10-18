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

    $.widget('mage.vippsLoginPopUp',{
        /**
         * Added clear timeout on trigger show
         */
        options: {
            type: 'popup',
            cacheKey: 'vipps_login_data',
            idModal: '#popup-mpdal',
            responsive: true,
            innerScroll: true,
            buttons: [
                {
                    text: $.mage.__('No'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                },
                {
                    text: $.mage.__('Yes'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }
            ]
        },
        _init: function () {
            var popup = modal(this.options, $(this.options.idModal));
            var getKey = storage.get('vippsPopUpShow');

            if (this._getData().addressUpdated &&
                getKey() !== true) {
                $(this.options.idModal).modal("openModal");
                storage.set('vippsPopUpShow',true);
            }
        },
        /**
         * @param {Object} data
         */
        _saveData: function (data) {
            storage.set(this.options.cacheKey, data);
        },

        /**
         * @return {*}
         */
        _getData: function () {
            var data = storage.get(this.options.cacheKey)();

            if ($.isEmptyObject(data)) {
                data = {
                    'addressUpdated': false
                };
                this._saveData(data);
            }

            return data;
        }

    });

    return $.mage.vippsLoginPopUp;

});



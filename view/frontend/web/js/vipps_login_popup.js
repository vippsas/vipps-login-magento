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

define([
   'jquery',
   'Magento_Ui/js/modal/modal',
   'Magento_Customer/js/customer-data',
   'mage/storage',
   'mage/translate'
], function ($, modal, customerData, storage, $t) {
    'use strict';

    $.widget('mage.vippsLoginPopUp', {
        /**
         * Added clear timeout on trigger show
         */
        options: {
            type: 'popup',
            cacheKey: 'vipps_login_data',
            idModal: '#popup-modal',
            callUrl: 'vipps/login/addressUpdate',
            paramCall: 'sync_address_mode',
            responsive: true,
            innerScroll: true,
            modalClass: 'vipps-popup',
            checkboxId: '#remember_choice',
            accountClass: '.account',
            buttons: [
                {
                    text: $.mage.__('Do nothing'),
                    class: '',
                    click: function () {
                        this.closeModal();
                        customerData.set('sync_address_mode', 2)
                    }
                },
                {
                    text: $.mage.__('Update'),
                    class: '',
                    click: function () {
                        this.closeModal();
                        customerData.set('sync_address_mode', 0)
                    }
                }
            ]
        },
        _init: function () {
            var vippsData = customerData.get('vipps_login_data');

            modal(this.options, $(this.options.idModal));
            this.update(vippsData());

            vippsData.subscribe(function (updatedData) {
                this.update(updatedData);
            }, this);
        },
        update: function(updateData) {
            var self = this;

            if (updateData.show_popup && $(this.options.accountClass).length) {
                this.setDataAddr();
                $(this.options.idModal).modal("openModal").on('modalclosed', function () {
                    self.sendData();
                });
                $(this.options.idModal).show();
            } else {
                $(this.options.idModal).hide();
            }
        },
        sendData: function () {
            storage.post(
                'vipps/login/addressUpdate',
                JSON.stringify({
                   'sync_address_mode': customerData.get('sync_address_mode')(),
                   'sync_address_remember': customerData.get('sync_address_remember')()
                }),
                1,
                'json'
            );
        },
        setDataAddr: function () {
            var addresses = customerData.get(this.options.cacheKey)();
            var customerName = customerData.get('customer')();
            if (addresses.newAddress || addresses.oldAddress) {
                $(this.options.idModal).find('.vipps-address').append(
                    '<ul>' +
                    '<li><strong>' + $t('New address from Vipps') + '</strong></li>' +
                    '<li>' + customerName.fullname + '</li>' +
                    '<li>' + addresses.newAddress.street + '</li>' +
                    '<li>' + addresses.newAddress.postalcode + ', ' + addresses.newAddress.city + '</li>'
                    + '</h2>'
                );

                $(this.options.idModal).find('.old-address').append(
                    '<ul>' +
                    '<li><strong>' + $t('Old address') + '</strong></li>' +
                    '<li>' + customerName.fullname + '</li>' +
                    '<li>' + addresses.oldAddress.street + '</li>' +
                    '<li>' + addresses.oldAddress.postalcode + ', ' + addresses.oldAddress.city + '</li>'
                    + '</h2>'
                );

                $('.' + this.options.modalClass).find('.modal-footer').prepend(
                    '<label for="remember_choice" class="remember-choice-holder">' +
                    '<input type="checkbox" name="remember_choice" id="remember_choice">' +
                    $t('Remember my choice') + '</label>'
                );
                this.checkboxHandler();
            }

        },
        checkboxHandler: function () {
            var checkbox = $(this.options.checkboxId);
            checkbox.on('change', function () {
                if (this.checked) {
                    customerData.set('sync_address_remember', true);
                } else {
                    customerData.set('sync_address_remember', false);
                }
            });
        }

    });

    return $.mage.vippsLoginPopUp;
});

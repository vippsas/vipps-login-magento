/*
 * Copyright 2020 Vipps
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
 * IN THE SOFTWARE.
 */

define([
   'jquery',
   'Vipps_Login/js/model/full-screen-loader',
   'uiComponent',
   "Magento_Customer/js/customer-data"
], function ($, fullScreenLoader, Component, customerData) {
    'use strict';

    return Component.extend({
        options: {
            selectHolder: 'vipps_address',
            cacheKey: 'vipps_login_data',
            formNewAddress: '.form-address-edit',
            streetField: 'street',
            postCode: 'postalcode',
            city: 'city',
            telephone: 'telephone',
            countryId: 'country_id',
            vippsInputId: 'vipps_address_id'
        },
        initialize: function (config) {
            this._super();
            this.selectHolderChange();
            this.insertHiddenInput(config.vippsAddressId);
        },
        selectHolderChange: function () {
            var self = this;
            var addresessKey = customerData.get(this.options.cacheKey)(),
                addressesList = addresessKey.addresses;

            $('#' + this.options.selectHolder).change(function () {
                var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].value;
                var addressePos = self.findData(dataOption, addressesList);
                if (addressePos !== null) {
                    self.changeValue(addressesList[addressePos]);
                    self.insertHiddenInput(dataOption);
                }
            })
        },
        /**
         * @return {number} position of needed array
         * @return {array} array of list
         */
        findData: function (value, dataList) {
            if (dataList === undefined) {
                return null;
            }
            var data = dataList.map( function(item) {
                return item.id;
            });
            return data.indexOf(value);
        },
        /**
         * @return {obj} data exactly same array
         */
        changeValue: function (props) {
           if(props !== undefined) {
               for( var key in props) {
                   if (key !== this.options.streetField ||
                       key !== this.options.postCode ||
                       key !== this.options.countryId) {
                       $(this.options.formNewAddress).find('input[name='+key+']').val(props[key]).change();
                   } if (key === this.options.streetField) {
                       $(this.options.formNewAddress).find('.field.street input').eq(0).val(props[key]).change();
                   } if (key === this.options.postCode) {
                       $(this.options.formNewAddress).find('input[name=postcode]').val(props[key]).change();
                   } if (key === this.options.countryId) {
                       $(this.options.formNewAddress).find('select[name='+ this.options.countryId + ']').val(props[key]).change();
                   }
               }
           }
        },
        /**
         * @return {text} itemText
         */
        insertHiddenInput: function (itemValue) {
            if(!$('#' + this.options.vippsInputId).length) {
                $(this.options.formNewAddress).append(
                    '<input name="vipps_address_id" id="vipps_address_id" type="text" hidden>'
                );
            }
            $('#' + this.options.vippsInputId).val(itemValue);
        }
    })
});
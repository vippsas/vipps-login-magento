/*
 * Copyright 2018 Vipps
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
       'underscore',
       'uiRegistry',
       'Magento_Ui/js/form/element/select',
       'jquery',
       'ko'
   ], function (_, registry, Select, $, ko) {
       'use strict';

        return Select.extend({
             defaults: {
                 imports: {
                     update: '${ $.parentName }.vipps_addresses_list:value'
                 }
             },
             options: {
                 selectHolder: 'vipps_address',
                 shippingForm: '#co-shipping-form',
                 streetField: 'street',
                 postCode: 'postalcode',
                 city: 'city',
                 telephone: 'telephone',
                 countryId: 'country_id',
                 vippsInputId: 'custom_attributes[vipps_address_id]',
                 showHideSelect: null
             },

             /**
              * Extends instance with defaults, extends config with formatted values
              *     and options, and invokes initialize method of AbstractElement class.
              *     If instance's 'customEntry' property is set to true, calls 'initInput'
              */
             initialize: function () {
                 this._super();

                 return this;
             },
             /** @inheritdoc */
             initObservable: function () {
                 this._super();

                 return this;
             },
             onUpdate: function (value) {
                 console.log('Selected Value: ' + value);
             },


             selectHolderChange: function () {
                 var self = this;

                 $(self.options.shippingForm).find('select[name='+ self.options.vippsInputId + ']').change(function (option) {
                     //var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].value;
                     //var addressePos = self.findData(dataOption, addressesList);
                     //self.changeValue(addressesList[addressePos]);
                     //self.insertHiddenInput(dataOption);
                     console.log('sss');
                 })
             },
             setDataForm: function (selectedId) {
                 var self = this,
                     addresessKey = CustomerData.get('vipps_login_data')(),
                     addressesList = addresessKey.addresses,
                     preselectAddresse = this.findData(selectedId,addressesList),
                     selectedAddresse = addressesList[preselectAddresse];

                 for (var key in selectedAddresse) {
                     if (key !== self.options.streetField ||
                         key !== self.options.postCode ||
                         key !== self.options.countryId) {
                         $(self.options.shippingForm).find('input[name='+key+']').val(selectedAddresse[key]).change();
                     } if (key === self.options.streetField) {
                         $(self.options.shippingForm).find('.field.street input').eq(0).val(selectedAddresse[key]).change();
                     } if (key === self.options.postCode) {
                         $(self.options.shippingForm).find('input[name=postcode]').val(selectedAddresse[key]).change();
                     } if (key === self.options.countryId) {
                         $(self.options.shippingForm).find('select[name='+ self.options.countryId + ']').val(selectedAddresse[key]).change();
                     }
                 }
                 var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].value;
                 self.insertHiddenInput(dataOption);
             },
             filter: function (value, field) {
                 console.log();

                 //this.setOptions(result);
             }
        });
});


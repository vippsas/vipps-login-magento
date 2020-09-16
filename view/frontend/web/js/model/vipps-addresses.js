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

/**
 * @api
 */
define([
       'underscore',
       'jquery',
       'uiRegistry',
       'Magento_Ui/js/form/element/select'
   ], function (_,  $, registry, Select) {
       'use strict';

        return Select.extend({
             defaults: {
                 imports: {
                     update: '${ $.parentName }.vipps_addresses_list:value'
                 }
             },
             elements: {
                 postcode: 'postcode',
                 city: 'city',
                 telephone: 'telephone',
                 country_id: 'country_id'
             },
             addressData: [],
             onUpdate: function (value) {
                 var self = this;

                 if (this.addressData.length > 0 ) {
                     var selectedAddress = this.addressData[value];
                     if (selectedAddress !== undefined) {
                         for (var prop in self.elements) {
                             var uiElem = registry.get(this.parentName + '.' + self.elements[prop]);
                             if (uiElem !== undefined) {
                                 uiElem.value(selectedAddress[prop]);
                             }
                         }

                         var uiElemStreet = registry.get(this.parentName + '.street');
                         if (uiElemStreet !== undefined) {
                             uiElemStreet.getChild(0).value(selectedAddress['street']);
                         }
                     }
                 }
             },
             /**
              * {@inheritdoc}
              *
              * @param {Array} data
              * @returns {Object} Chainable
              */
             setOptions: function (data) {
                 var self = this;
                 data.forEach(function(option){
                     if (option.address !== undefined) {
                         self.addressData[option.value] = option.address;
                     }
                 });
                 return this._super();
             }
        });
});


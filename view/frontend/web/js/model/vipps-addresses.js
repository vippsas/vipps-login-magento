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
       'underscore',
       'jquery',
       'Magento_Customer/js/customer-data',
       'uiRegistry',
       'Magento_Ui/js/form/element/select'
   ], function (_,  $, CustomerData, registry, Select) {
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
             onUpdate: function (value) {
                 var self = this,
                     vippsData = CustomerData.get('vipps_login_data')(),
                     addressesList = vippsData.addresses,
                     selectedAddress = this.findData(value, addressesList);

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
             },
             /**
              * @return {number} position of needed array
              * @return {array} array of list
              */
             findData: function (value, dataList) {
                 var data = dataList.map( function(item) {
                     return item.id;
                 });
                 var index = data.indexOf(value);

                 return dataList[index];
             }
        });
});


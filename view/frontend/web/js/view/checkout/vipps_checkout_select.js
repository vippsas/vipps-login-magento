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
   'uiComponent',
   'Magento_Customer/js/model/customer',
   'Magento_Customer/js/customer-data',
   'uiRegistry'
], function ($, Component, customer, CustomerData, registry) {
   'use strict';
    // "shippingAddress"
   return Component.extend({
       options: {
           selectHolder: 'vipps_address',
           shippingForm: '#co-shipping-form',
           streetField: 'street',
           postCode: 'postalcode',
           city: 'city',
           telephone: 'telephone',
           countryId: 'country_id',
           showHideSelect: null
       },
       initialize: function () {
        this._super();
        this.observe(['dataList','selectedAddresse','showHideSelect']);
        this.checklinkedAccount();
       },
       checklinkedAccount: function () {
            var addresessKey = CustomerData.get('vipps_login_data')();
            if(addresessKey.linked) {
                this.showHideSelect(true);
            }
       },
       getDataList: function() {
           var listAddresses = CustomerData.get('vipps_login_data')();
           this.dataList(listAddresses.addresses);
          return listAddresses.addresses
       },
       selectHolderChange: function (data, event) {
           this.setDataForm(event.target.selectedOptions[0].value);
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
       },
       /**
        * @return {number} position of needed array
        * @return {array} array of list
        */
       findData: function (value, dataList) {
           var data = dataList.map( function(item) {
               return item.id;
           });
           return data.indexOf(value);
       },
   });
   }
);
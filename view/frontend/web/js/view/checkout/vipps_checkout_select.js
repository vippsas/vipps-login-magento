/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
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
           countryId: 'country_id'
       },
       initialize: function () {
        this._super();
        this.observe(['dataList','selectedAddresse']);

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
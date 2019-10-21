/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

define([
   'jquery',
   'uiComponent',
   'Magento_Customer/js/model/customer',
   'Magento_Customer/js/customer-data',

], function ($, Component, customer, CustomerData) {
   'use strict';

   return Component.extend({
       options: {
           selectHolder: 'vipps_address',
       },
       initialize: function () {
        this._super();
        this.observe(['dataList','selectedAddresse'])
       },
       getDataList: function() {
           var listAddresses = CustomerData.get('vipps_login_data')();
           this.dataList(listAddresses.addresses)
          return listAddresses.addresses
       },
       selectHolderChange: function (data, event) {
           this.selectedAddresse(event.target.selectedOptions[0].value);
           console.log(event.target.selectedOptions[0].value)

       }
   });
   }
);
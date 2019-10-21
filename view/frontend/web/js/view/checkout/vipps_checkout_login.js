/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

define([
   'jquery',
   'uiComponent',
   'Magento_Customer/js/model/customer',
   'mage/url',
], function ($, Component, customer, url) {
   'use strict';

   return Component.extend({
       options: {
           isCustomerLoggedIn: customer.isLoggedIn,
       },
       initialize: function () {
        this._super();
        console.log('______', this.options.isCustomerLoggedIn())

       },
       checkLoginUser: function () {
           return this.options.isCustomerLoggedIn()
       },
       getBaseUrl: function() {
           return url.build('vipps/login/index');
       },

   });
   }
);
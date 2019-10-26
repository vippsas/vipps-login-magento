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
           countryId: 'country_id',
           vippsInputId: 'custom_attributes[vipps_address_id]',
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
           var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].value;
           self.insertHiddenInput(dataOption);
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
       /**
        * @return {text} itemText
        */
       insertHiddenInput: function (itemValue) {
           $(this.options.shippingForm).
               find("input[name='" + this.options.vippsInputId + "']").val(itemValue).change();
       }
   });
   }
);
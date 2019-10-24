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
                self.changeValue(addressesList[addressePos]);
                self.insertHiddenInput(dataOption);
            })
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
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
            formNewAddress: 'form-address-edit',
            streetField: 'street_1',
            postCode: 'zip'
        },
        initialize: function () {
            this._super();
            this.selectHolderChange();
        },
        selectHolderChange: function () {
            var self = this;
            var addresessKey = customerData.get(this.options.cacheKey)(),
                addressesList = addresessKey.addresses;

            $('#' + this.options.selectHolder).change(function () {
                var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].value;
                var addressePos = self.findData(dataOption, addressesList);
                self.changeValue(addressesList[addressePos])
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
            var propsStreet = $('.' + this.options.formNewAddress).find('#' + this.options.streetField);
            var propsZip = $('.' + this.options.formNewAddress).find('#' + this.options.postCode);
            propsStreet.val(props.street);
            propsZip.val(props.postalcode);
        }

    });
});
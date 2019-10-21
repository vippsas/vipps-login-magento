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
            postCode: 'zip',
            vippsInputId: 'vipps_address_id'
        },
        initialize: function () {
            this._super();
            this.selectHolderChange();
            this.insertHiddenInput();
        },
        selectHolderChange: function () {
            var self = this;
            var addresessKey = customerData.get(this.options.cacheKey)(),
                addressesList = addresessKey.addresses;

            $('#' + this.options.selectHolder).change(function () {
                var dataOption = $('#' + self.options.selectHolder + " option:selected")[0].value;
                var dataOptionText = $('#' + self.options.selectHolder + " option:selected")[0].textContent;
                var addressePos = self.findData(dataOption, addressesList);
                self.changeValue(addressesList[addressePos]);
                self.insertHiddenInput(dataOptionText);
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
        },
        /**
         * @return {text} itemText
         */
        insertHiddenInput: function (itemText) {
            if(!$('#' + this.options.vippsInputId).length) {
                $('.' + this.options.formNewAddress).append(
                    '<input name="vipps_address_id" id="vipps_address_id" type="text" hidden>'
                );
            } else {
                $('#' + this.options.vippsInputId).val(itemText);
            }

        }

    });
});
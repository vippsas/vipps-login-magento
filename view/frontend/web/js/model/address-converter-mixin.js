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

/*jshint browser:true jquery:true*/
/*global alert*/
define([
           'jquery',
           'mage/utils/wrapper'
       ], function ($, wrapper) {
    'use strict';

    return function (addressConverter) {
        addressConverter.formAddressDataToQuoteAddress = wrapper.wrap(addressConverter.formAddressDataToQuoteAddress, function (_super, formData) {
            if (formData && formData.custom_attributes !== undefined) {
                if (formData.custom_attributes['vipps_address_box'] !== undefined) {
                    formData.custom_attributes['vipps_address_box'] =
                        parseInt(formData.custom_attributes['vipps_address_box']);
                }
            }
            return _super(formData);
        });
        return addressConverter;
    };
});
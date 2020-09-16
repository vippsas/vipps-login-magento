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

define([
    'jquery',
    'mage/template',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/translate'
], function ($, mageTemplate, customerData, url, $t) {
    'use strict';

    return function () {
        var customer = customerData.get('customer');

        var renderButton = function (user) {
            if (user.firstname) {
                $('#vipps_login_widget').empty()
            } else if ( $('#vipps_login_widget').children().length === 0) {
                var template = '<div class="login-vipps-holder checkout-page">' +
                               '    <div class="login-vipps">' +
                               '        <form action="' + url.build("vipps/login/index") + '" method="post">' +
                               '            <button type="submit" class="action create primary vipps-btn">' +
                               '                <span>' + $t("Sign in with") + '</span>' +
                               '                <span class="icon-vipps"></span>' +
                               '            </button>' +
                               '        </form>' +
                               '    </div>' +
                               '</div>';
                var tmpl = mageTemplate(template);
                $('#vipps_login_widget').append(tmpl);
            }
        };

        renderButton(customer());

        customer.subscribe(function (updateCustomer) {
            renderButton(updateCustomer);
        }, this);
    };
});
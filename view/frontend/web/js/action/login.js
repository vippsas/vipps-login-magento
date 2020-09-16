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
       'mage/storage',
       'Magento_Ui/js/model/messageList',
       'Magento_Customer/js/customer-data'
], function ($, storage, globalMessageList, customerData) {
    'use strict';

    var callbacks = [],

    /**
     * @param {Object} loginData
     * @param {String} redirectUrl
     * @param {*} isGlobal
     * @param {Object} messageContainer
     */
    action = function (loginData, postUrl, redirectUrl, isGlobal, messageContainer) {
        messageContainer = messageContainer || globalMessageList;

        return storage.post(
            postUrl,
            JSON.stringify(loginData),
            isGlobal,
            'json'
        ).done(function (response) {
            if (response.errors) {
                messageContainer.addErrorMessage(response);
                callbacks.forEach(function (callback) {
                    callback(loginData);
                });
            } else {
                callbacks.forEach(function (callback) {
                    callback(loginData);
                });
                customerData.invalidate(['customer']);

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else if (response.redirectUrl) {
                    window.location.href = response.redirectUrl;
                } else {
                    location.reload();
                }
            }
        }).fail(function () {
            messageContainer.addErrorMessage({
                'message': 'Could not authenticate. Please try again later'
            });
            callbacks.forEach(function (callback) {
                callback(loginData);
            });
        });
    };

    /**
     * @param {Function} callback
     */
    action.registerLoginCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});

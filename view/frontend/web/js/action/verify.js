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
    'Vipps_Login/js/action/login',
    'Vipps_Login/js/model/full-screen-loader',
    'mage/storage',
    'Magento_Ui/js/modal/alert',
    'uiComponent',
    'mage/translate'
],function($, loginAction, fullScreenLoader, storage, alert,Component, $t) {
    'use strict';
    var self = this;

    return Component.extend({
        options: {
            verifyPasswordForm: 'verify-password-form',
            verifyEmailForm: 'verify-email-form',
            urlLoginConfirmation: 'vipps/login/emailConfirmation',
            urlPasswordConfirmation: 'vipps/login/passwordConfirm'
        },
        initialize: function () {
            this._super();

            this.triggerSubmitForm();
        },
        triggerSubmitForm: function () {
            self = this;
            $('form').submit(function (ev) {
                ev.preventDefault();
                if ($(this).validation() &&
                    $(this).validation('isValid')) {
                    self.submitForm(this);
                }
            })
        },
        /**
         * @param {Object} form for send correct request
         */
        submitForm: function (formId) {

            if (formId.id === this.options.verifyEmailForm) {
                self.loadderShowOrHide(true);
                storage.post(
                    self.options.urlLoginConfirmation,
                    JSON.stringify(self.fetchData(self.options.verifyEmailForm)),
                    1,
                    'json'
                ).done(function (response) {
                    if (response.error !== true) {
                        self.showMsgPopUp($t('Check your inbox'),response.message)
                    } else {
                        self.showMsgPopUp($t('An error occurred'),response.message);
                    }
                }).always(function () {
                    self.loadderShowOrHide();
                });
            }
            if (formId.id === this.options.verifyPasswordForm) {
                self.loadderShowOrHide(true);
                loginAction(self.fetchData(self.options.verifyPasswordForm),
                            self.options.urlPasswordConfirmation).done(function (response) {
                    if (response.error !== true) {
                        self.showMsgPopUp($t('Check your inbox'),response.message)
                    } else {
                        self.showMsgPopUp($t('An error occurred'),response.message);
                    }
                }).always(function () {
                    self.loadderShowOrHide();
                });
            }
        },
        /**
         * @param {name} name of needed form for get correct params
         * @return {*} data
         */
        fetchData: function (formId) {
            var loginData = {},
                formDataArray = $('#'+formId).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });
            return loginData
        },
        /**
         * @param {boolean} true or nothing for show or hide loader
         */
        loadderShowOrHide: function (show) {
            if (show) {
                fullScreenLoader.startLoader();
            } else {
                fullScreenLoader.stopLoader();
            }
        },
        /**
         * @param {text} Title of pop up
         * @param {text} msg in pop up
         */
        showMsgPopUp: function (title,msg) {
            alert({
                  title: $.mage.__(title),
                  content: $.mage.__(msg)
            });
        }

    });
});
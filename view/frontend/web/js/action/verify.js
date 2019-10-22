define([
    'jquery',
    'Vipps_Login/js/action/login',
    'Vipps_Login/js/model/full-screen-loader',
    'mage/storage',
    'Magento_Ui/js/modal/alert',
    'uiComponent',
],function($, loginAction, fullScreenLoader, storage, alert,Component) {
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
                    if (response.success) {
                        self.showMsgPopUp('Request was send',response.message)
                    } else {
                        self.showMsgPopUp('An error occurred',response.message);
                    }
                }).always(function () {
                    self.loadderShowOrHide();
                });
            }
            if (formId.id === this.options.verifyPasswordForm) {
                self.loadderShowOrHide(true);
                loginAction(self.fetchData(self.options.verifyPasswordForm),
                            self.options.urlPasswordConfirmation).done(function (response) {
                    if (response.success) {
                        self.showMsgPopUp('Request was send',response.message)
                    } else {
                        self.showMsgPopUp('An error occurred',response.message);
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
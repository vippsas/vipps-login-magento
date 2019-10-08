define([
    'jquery',
    'Vipps_Login/js/action/login',
    'Vipps_Login/js/model/full-screen-loader',
    'mage/storage',
],function($, loginAction, fullScreenLoader, storage) {
    $(document).ready(function() {
        $("#verify-password-form").submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            var loginData = {},
                formDataArray = $(this).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if ($(this).validation() && $(this).validation('isValid')) {
                fullScreenLoader.startLoader();
                loginAction(loginData, 'vipps/login/verifyAjax').always(function () {
                    fullScreenLoader.stopLoader();
                });
            }
        });

        $("#verify-email-form").submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            var loginData = {},
                formDataArray = $(this).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if ($(this).validation() && $(this).validation('isValid')) {
                fullScreenLoader.startLoader();
                storage.post(
                    'vipps/login/emailConfirmation',
                    JSON.stringify(loginData),
                    1,
                    'json'
                ).done(function (response) {
                    if (response.success) {
                        alert('message sent');
                    }
                }).always(function () {
                    fullScreenLoader.stopLoader();
                });
            }
        });
    });
});
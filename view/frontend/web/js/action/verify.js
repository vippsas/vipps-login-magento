define([
    'jquery',
    'Magento_Customer/js/action/login',
    'Vipps_Login/js/model/full-screen-loader'
],function($, loginAction, fullScreenLoader) {
    $(document).ready(function() {
        $("#verify-form").submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            var loginData = {},
                formDataArray = $(this).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if ($(this).validation() && $(this).validation('isValid')) {
                fullScreenLoader.startLoader();
                loginAction(loginData).always(function () {
                    fullScreenLoader.stopLoader();
                });
            }
        });
    });
});
define([
   'jquery',
   'uiComponent',
],function($,Component) {
    'use strict';

    return Component.extend({
        options: {
            formAccountCreate: 'form-create-account',
            linkToogle: 'link-toogle'
        },
        initialize: function () {
            this._super();
            this.toogle();
        },
        toogle: function () {
            var self = this;
            $('.' + this.options.linkToogle).on('click',function () {
               $('.' + self.options.formAccountCreate).slideToggle();
            });
        }
    });
});
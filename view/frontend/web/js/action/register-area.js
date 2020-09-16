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
   'uiComponent'
],function($, Component) {
    'use strict';

    return Component.extend({
        options: {
            formAccountCreate: '.form-create-account',
            linkToggle: '.link-toggle'
        },
        initialize: function () {
            this._super();
            this.cookieMessages = $.cookieStorage.get('mage-messages');
            if (this.cookieMessages && this.cookieMessages.length) {
                $(this.options.formAccountCreate).show();
            }
            this.toggle();
        },
        toggle: function () {
            if($(this.options.linkToggle).length) {
                var self = this;
                $(this.options.linkToggle).on('click',function () {
                    $(self.options.formAccountCreate).slideToggle();
                });
            } else  {
                $(this.options.formAccountCreate).show();
                $(this.options.formAccountCreate).find('.field.password').remove();
                $(this.options.formAccountCreate).find('.field.confirmation').remove();
            }
        }
    });
});
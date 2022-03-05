/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, fullScreenLoader) {
        'use strict';

        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'BuyBox_Payment/payment/form'
            },
            initObservable: function () {
                return this;
            },

            getCode: function () {
                return window.checkoutConfig.payment[this.item.method].code;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {}
                };
            },
            afterPlaceOrder: function () {
                fullScreenLoader.startLoader();
                // Get payment page URL from checkoutConfig and redirect
                window.location.href = window.checkoutConfig.payment[this.item.method].redirect_url
            }
        });
    }
);
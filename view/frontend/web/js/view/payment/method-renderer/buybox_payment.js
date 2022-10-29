/**
 * BuyBox Gift Card payment module for Magento
 *
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0.
 *
 * @package   BuyBox\Payment
 * @author    Studiolab <contact@studiolab.fr>
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      https://www.buybox.net/
 */
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

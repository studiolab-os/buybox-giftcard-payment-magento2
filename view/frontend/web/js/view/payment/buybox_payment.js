/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: window.checkoutConfig.payment.buybox_payment.code,
                component: 'BuyBox_Payment/js/view/payment/method-renderer/buybox_payment'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);

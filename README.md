# BuyBox Payment Module

BuyBox Gift Card payment gateway integration module for Magento 2.

## Technical feature

### ⚠️ Magento versions compatibility :

**Which version should I use ?**

| Magento Version                                              | BuyBox Payment Version   | Install                                                        |
|--------------------------------------------------------------|--------------------------|----------------------------------------------------------------|
| Magento **2.0.x** Opensource (CE) && Enterprise Edition (EE) | BuyBox Payment **2.0.x** | ```composer require buybox/giftcard-payment-magento2 ~2.0.0``` |
| Magento **2.1.x** Opensource (CE) && Enterprise Edition (EE) | BuyBox Payment **2.1.x** | ```composer require buybox/giftcard-payment-magento2 ~2.1.0``` |
| Magento **2.2.x** Opensource (CE) && Enterprise Edition (EE) | BuyBox Payment **2.2.x** | ```composer require buybox/giftcard-payment-magento2 ~2.2.0``` |
| Magento **2.3.x** Opensource (CE) && Enterprise Edition (EE) | BuyBox Payment **2.3.x** | ```composer require buybox/giftcard-payment-magento2 ~2.3.0``` |

### Installing

#### Using Composer
The easiest way to install the extension is to use Composer:

```bash
$ composer require buybox/giftcard-payment-magento2
$ bin/magento module:enable BuyBox_Payment
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
$ bin/magento setup:static-content:deploy
```


### Module configuration

1. Package details [composer.json](composer.json).
2. Module configuration details (sequence) in [module.xml](etc/module.xml).
3. Module configuration available through Stores->Configuration [system.xml](etc/adminhtml/system.xml)

Payment gateway module depends on `Sales`, `Payment` and `Checkout` Magento modules. For more module configuration
details, please look
through [module development docs](http://devdocs.magento.com/guides/v2.0/extension-dev-guide/module-load-order.html).

### Gateway configuration

On the next step, we might specify gateway domain configuration in [config.xml](etc/config.xml).

##### Let's look into configuration attributes:

* <code>debug</code> enables debug mode by default, e.g log for request/response
* <code>active</code> is payment active by default
* <code>model</code> `Payment Method Facade` used for integration with `Sales` and `Checkout` modules
* <code>merchant_gateway_key</code> encrypted merchant credential
* <code>order_status</code> default order status
* <code>payment_action</code> default action of payment
* <code>title</code> default title for a payment method
* <code>currency</code> supported currency
* <code>can_authorize</code> whether payment method supports authorization
* <code>can_capture</code> whether payment method supports capture
* <code>can_void</code> whether payment method supports void
* <code>can_use_checkout</code> checkout availability
* <code>is_gateway</code> is an integration with gateway
* <code>sort_order</code> payment method order position on checkout/system configuration pages
* <code>debugReplaceKeys</code> request/response fields, which will be masked in log
* <code>paymentInfoKeys</code> transaction request/response fields displayed on payment information block
* <code>privateInfoKeys</code> paymentInfoKeys fields which should not be displayed in customer payment information
  block

### Dependency Injection configuration

> To get more details about dependency injection configuration in Magento 2, please see [DI docs](http://devdocs.magento.com/guides/v2.0/extension-dev-guide/depend-inj.html).

In a case of Payment Gateway, DI configuration is used to define pools of `Gateway Commands` with related infrastructure
and to configure `Payment Method Facade` (used by `Sales` and `Checkout` modules to perform commands)


## Contributors

[Studiolab](https://studiolab.fr)

## License

[Open Source License](LICENSE.txt)
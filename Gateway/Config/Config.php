<?php

/**
 * BuyBox payment module for Magento
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

declare(strict_types=1);

namespace BuyBox\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'buybox_payment';

    const DEFAULT_ROUTE_PATH = 'buybox/payment/%s';

    const DEFAULT_NVP_URL = 'https://%s.buybox.net/secure/express-checkout/nvp.php';

    /**
     * Environment
     */
    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * Action List
     */
    const ACTION_REDIRECT = 'redirect';
    const ACTION_RETURN = 'return';
    const ACTION_CANCEL = 'cancel';

    /**
     * Config keys.
     */
    const KEY_IS_ACTIVE = 'active';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_DEBUG = 'debug';
    const KEY_TITLE = 'title';
    const KEY_ENVIRONMENT = 'environment';
    const KEY_API_USERNAME = 'api_username';
    const KEY_API_PASSWORD = 'api_password';
    const KEY_API_SIGNATURE = 'api_signature';
    const KEY_SERVICE_DOMAIN = 'service_domain';
    const KEY_ORDER_STATUS = 'order_status';
    const KEY_PAYMENT_ACTION = 'payment_action';

    const METHOD_DO_VOID = 'DoVoid';
    const METHOD_DO_CAPTURE = 'DoCapture';
    const METHOD_DO_AUTHORISATION = 'DoAuthorization';
    const METHOD_DO_EXPRESS_CHECKOUT_PAYMENT = 'DoExpressCheckoutPayment';
    const METHOD_SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';
    const METHOD_REFUND_TRANSACTION = 'RefundTransaction';

    /**
     * Different payment actions.
     */
    const PAYMENT_ACTION_AUTHORIZE = 'authorize';
    const PAYMENT_ACTION_SALE = 'sale';

    const PAYMENT_ACTION_MAP = [
        self::PAYMENT_ACTION_AUTHORIZE => 'Authorization',
        self::PAYMENT_ACTION_SALE => 'Sale',
    ];

    /**
     * @var UrlInterface $url
     */
    private $url;

    /**
     * @var string $pathPattern
     */
    private $pathPattern;

    /**
     * @var string|null $methodCode
     */
    private $methodCode;


    /**
     * Config constructor.
     *
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig,
        string $methodCode = self::CODE,
        string $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);

        $this->url = $url;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
    }

    /**
     * @inheritdoc
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * @inheritdoc
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFieldPath(string $field): string
    {
        return sprintf($this->pathPattern, $this->methodCode, $field);
    }

    /**
     * Get Is Active.
     *
     * @param null $storeId
     * @return bool
     */
    public function getIsActive($storeId = null): bool
    {
        return (bool)(int)$this->get(self::KEY_IS_ACTIVE, '', $storeId);
    }

    /**
     * @param  $field
     * @param null $default
     * @param null $storeId
     * @return mixed|null
     */
    public function get($field, $default = null, $storeId = null)
    {
        $value = parent::getValue($field, $storeId);

        if ($value === null) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Get Is Active.
     *
     * @param null $storeId
     * @return bool
     */
    public function getDebug($storeId = null): bool
    {
        return (bool)(int)$this->get(self::KEY_DEBUG, '', $storeId);
    }

    /**
     * Get Sort Order.
     *
     * @param null $storeId
     * @return int
     */
    public function getSortOrder($storeId = null): int
    {
        return (int)$this->get(self::KEY_SORT_ORDER, '', $storeId);
    }

    /**
     * Get Api Username.
     *
     * @param  $storeId
     * @return string
     */
    public function getApiUsername($storeId = null): string
    {
        return (string)$this->get(self::KEY_API_USERNAME, '', $storeId);
    }

    /**
     * Get Api Password.
     *
     * @param  $storeId
     * @return string
     */
    public function getApiPassword($storeId = null): string
    {
        return (string)$this->get(self::KEY_API_PASSWORD, '', $storeId);
    }

    /**
     * Get Api Signature.
     *
     * @param $storeId
     *
     * @return string
     */
    public function getApiSignature($storeId = null): string
    {
        return (string)$this->get(self::KEY_API_SIGNATURE, '', $storeId);
    }

    /**
     * Get Service Domaine.
     *
     * @param  $storeId
     * @return string
     */
    public function getServiceDomain($storeId = null): string
    {
        return (string)$this->get(self::KEY_SERVICE_DOMAIN, '', $storeId);
    }

    /**
     * Get Order Status.
     *
     * @param  $storeId
     * @return string
     */
    public function getOrderStatus($storeId = null): string
    {
        return (string)$this->get(self::KEY_ORDER_STATUS, '', $storeId);
    }

    /**
     * Get Payment Action.
     *
     * @param  $storeId
     * @return string
     */
    public function getPaymentAction($storeId = null): string
    {
        return (string)$this->get(self::KEY_PAYMENT_ACTION, '', $storeId);
    }

    /**
     * Get Payment Action.
     *
     * @param string $action
     * @return string
     */
    public function getUrl(string $action): string
    {
        return $this->url->getUrl(sprintf(self::DEFAULT_ROUTE_PATH, $action));
    }

    /**
     * Get Api Endpoint.
     *
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return sprintf(
            self::DEFAULT_NVP_URL,
            $this->getEnvironment() == self::ENVIRONMENT_SANDBOX ? 'sandbox' : 'www2'
        );
    }

    /**
     * Get Environment.
     *
     * @param  $storeId
     * @return string
     */
    public function getEnvironment($storeId = null): string
    {
        return (string)$this->get(self::KEY_ENVIRONMENT, '', $storeId);
    }

    public function getAuthenticationParams(): array
    {
        return [
            'VERSION' => '1.0',
            'USER' => $this->getApiUsername(),
            'PWD' => $this->getApiPassword(),
            'SIGNATURE' => $this->getApiSignature(),
        ];
    }
}

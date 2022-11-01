<?php
/**
 * BuyBox Gift Card payment module for Magento.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0.
 *
 * @author    Studiolab <contact@studiolab.fr>
 * @license   http://opensource.org/licenses/OSL-3.0
 *
 * @see      https://www.buybox.net/
 */

declare(strict_types=1);

namespace BuyBox\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    public const CODE = 'buybox_payment';

    public const DEFAULT_ROUTE_PATH = 'buybox/payment/%s';

    public const DEFAULT_NVP_URL = 'https://%s.buybox.net/secure/express-checkout/nvp.php';

    /**
     * Environment.
     */
    public const ENVIRONMENT_SANDBOX = 'sandbox';
    public const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * sub domain.
     */
    public const SUB_DOMAIN_SANDBOX = 'sandbox';
    public const SUB_DOMAIN_PRODUCTION = 'www2';

    /**
     * Action List.
     */
    public const ACTION_REDIRECT = 'redirect';
    public const ACTION_RETURN = 'return';
    public const ACTION_CANCEL = 'cancel';

    /**
     * Config keys.
     */
    public const KEY_IS_ACTIVE = 'active';
    public const KEY_SORT_ORDER = 'sort_order';
    public const KEY_DEBUG = 'debug';
    public const KEY_TITLE = 'title';
    public const KEY_ENVIRONMENT = 'environment';
    public const KEY_API_USERNAME = 'api_username';
    public const KEY_API_PASSWORD = 'api_password';
    public const KEY_API_SIGNATURE = 'api_signature';
    public const KEY_SERVICE_DOMAIN = 'service_domain';
    public const KEY_ORDER_STATUS = 'order_status';
    public const KEY_PAYMENT_ACTION = 'payment_action';
    public const KEY_TOKEN = 'token';
    public const KEY_PAYER_ID = 'PayerID';

    /**
     * Payment Methods.
     */
    public const METHOD_DO_VOID = 'DoVoid';
    public const METHOD_DO_CAPTURE = 'DoCapture';
    public const METHOD_DO_AUTHORISATION = 'DoAuthorization';
    public const METHOD_DO_EXPRESS_CHECKOUT_PAYMENT = 'DoExpressCheckoutPayment';
    public const METHOD_SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';
    public const METHOD_REFUND_TRANSACTION = 'RefundTransaction';

    /**
     * Different payment actions.
     */
    public const PAYMENT_ACTION_AUTHORIZE = 'authorize';
    public const PAYMENT_ACTION_SALE = 'sale';

    public const PAYMENT_ACTION_MAP = [
        self::PAYMENT_ACTION_AUTHORIZE => 'Authorization',
        self::PAYMENT_ACTION_SALE      => 'Sale',
    ];

    /**
     * Refund.
     */
    public const REFUND_TYPE_FULL = 'Full';
    public const REFUND_TYPE_PARTIAL = 'Partial';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var string
     */
    private $pathPattern;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * Config constructor.
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
     * {@inheritdoc}
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    public function getFieldPath(string $field): string
    {
        return sprintf($this->pathPattern, $this->methodCode, $field);
    }

    /**
     * Get Is Active.
     *
     * @param null $storeId
     */
    public function getIsActive($storeId = null): bool
    {
        return (bool) (int) $this->get(self::KEY_IS_ACTIVE, '', $storeId);
    }

    /**
     * @param      $field
     * @param null $default
     * @param null $storeId
     *
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
     */
    public function getDebug($storeId = null): bool
    {
        return (bool) (int) $this->get(self::KEY_DEBUG, '', $storeId);
    }

    /**
     * Get Sort Order.
     *
     * @param null $storeId
     */
    public function getSortOrder($storeId = null): int
    {
        return (int) $this->get(self::KEY_SORT_ORDER, '', $storeId);
    }

    /**
     * Get Api Username.
     *
     * @param $storeId
     */
    public function getApiUsername($storeId = null): string
    {
        return (string) $this->get(self::KEY_API_USERNAME, '', $storeId);
    }

    /**
     * Get Api Password.
     *
     * @param $storeId
     */
    public function getApiPassword($storeId = null): string
    {
        return (string) $this->get(self::KEY_API_PASSWORD, '', $storeId);
    }

    /**
     * Get Api Signature.
     *
     * @param $storeId
     */
    public function getApiSignature($storeId = null): string
    {
        return (string) $this->get(self::KEY_API_SIGNATURE, '', $storeId);
    }

    /**
     * Get Service Domaine.
     *
     * @param $storeId
     */
    public function getServiceDomain($storeId = null): string
    {
        return (string) $this->get(self::KEY_SERVICE_DOMAIN, '', $storeId);
    }

    /**
     * Get Order Status.
     *
     * @param $storeId
     */
    public function getOrderStatus($storeId = null): string
    {
        return (string) $this->get(self::KEY_ORDER_STATUS, '', $storeId);
    }

    /**
     * Get Payment Action.
     *
     * @param $storeId
     */
    public function getPaymentAction($storeId = null): string
    {
        return (string) $this->get(self::KEY_PAYMENT_ACTION, '', $storeId);
    }

    /**
     * Get Payment Action.
     */
    public function getUrl(string $action): string
    {
        return $this->url->getUrl(sprintf(self::DEFAULT_ROUTE_PATH, $action));
    }

    /**
     * Get Api Endpoint.
     */
    public function getApiEndpoint(): string
    {
        return sprintf(
            self::DEFAULT_NVP_URL,
            $this->getEnvironment() == self::ENVIRONMENT_SANDBOX
                ? self::SUB_DOMAIN_SANDBOX
                : self::SUB_DOMAIN_PRODUCTION
        );
    }

    /**
     * Get Environment.
     *
     * @param $storeId
     */
    public function getEnvironment($storeId = null): string
    {
        return (string) $this->get(self::KEY_ENVIRONMENT, '', $storeId);
    }

    public function getAuthenticationParams(): array
    {
        return [
            'VERSION'   => '1.0',
            'USER'      => $this->getApiUsername(),
            'PWD'       => $this->getApiPassword(),
            'SIGNATURE' => $this->getApiSignature(),
        ];
    }
}

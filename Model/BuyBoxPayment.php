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

namespace BuyBox\Payment\Model;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\Service\CreateInvoiceService;
use BuyBox\Payment\Model\Service\ValidatePaymentService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Zend_Http_Client;

class BuyBoxPayment
{
    const ORDER_STATUS_AUTHORIZED_CODE = 'authorized';
    const ORDER_STATUS_AUTHORIZED_LABEL = 'Authorized';
    const ORDER_STATE_AUTHORIZED_CODE = 'authorized';
    const ORDER_STATE_AUTHORIZED_LABEL = 'Authorized';

    /**
     * @var ValidatePaymentService
     */
    private $validatePaymentService;

    /**
     * @var CreateInvoiceService
     */
    private $createInvoiceService;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var Config
     */
    private $config;


    /**
     * @param ValidatePaymentService $validatePaymentService
     * @param CreateInvoiceService $createInvoiceService
     * @param RestClient $restClient
     * @param Config $config
     */
    public function __construct(
        ValidatePaymentService $validatePaymentService,
        CreateInvoiceService $createInvoiceService,
        RestClient $restClient,
        Config $config
    ) {
        $this->validatePaymentService = $validatePaymentService;
        $this->createInvoiceService = $createInvoiceService;
        $this->restClient = $restClient;
        $this->config = $config;
    }

    /**
     * Validate Order Payment.
     *
     * @param Order $order
     * @param $params
     * @return void
     * @throws LocalizedException
     */
    public function process(Order $order, $params)
    {
        $result = $this->doExpressCheckout($order, $params);

        if ($this->config->getPaymentAction() == Config::PAYMENT_ACTION_SALE) {
            $this->validatePaymentService->execute(
                $order,
                $result,
                TransactionInterface::TYPE_CAPTURE
            );
            $this->createInvoiceService->execute($order);
        } elseif ($this->config->getPaymentAction() == Config::PAYMENT_ACTION_AUTHORIZE) {
            $this->validatePaymentService->execute(
                $order,
                $result,
                TransactionInterface::TYPE_AUTH
            );
        } else {
            throw new LocalizedException(__('Payment action is not supported. Please contact client service'));
        }
    }

    /**
     * Do Authorization.
     *
     * @param OrderInterface $order
     * @param $params
     * @return array|null
     * @throws LocalizedException
     */
    public function doAuthorization(OrderInterface $order, $params)
    {
        $params = array_merge([
            RestClient::KEY_METHOD => Config::METHOD_DO_AUTHORISATION,
            RestClient::KEY_PAYER_ID => $params['PayerID'],
            RestClient::KEY_TOKEN => $params['token'],
        ], $this->getDefaultParams($order), $this->config->getAuthenticationParams());

        return $this->restClient->callApi(
            $this->config->getApiEndpoint(),
            $params,
            Zend_Http_Client::POST
        );
    }

    /**
     * Do Express Checkout.
     *
     * @param OrderInterface $order
     * @param $params
     * @return array|null
     * @throws LocalizedException
     */
    public function doExpressCheckout(OrderInterface $order, $params)
    {
        $params = array_merge([
            RestClient::KEY_METHOD => Config::METHOD_DO_EXPRESS_CHECKOUT_PAYMENT,
            RestClient::KEY_PAYER_ID => $params['PayerID'],
            RestClient::KEY_TOKEN => $params['token']
        ], $this->getDefaultParams($order), $this->config->getAuthenticationParams());

        return $this->restClient->callApi(
            $this->config->getApiEndpoint(),
            $params,
            Zend_Http_Client::POST
        );
    }

    /**
     * Get Default Params.
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getDefaultParams(OrderInterface $order): array
    {
        return [
            RestClient::KEY_PAYMENT_ACTION => Config::PAYMENT_ACTION_MAP[$this->config->getPaymentAction()],
            RestClient::KEY_AMOUNT => $order->getGrandTotal(),
            RestClient::KEY_CURRENCY_CODE => $order->getStoreCurrencyCode()
        ];
    }
}

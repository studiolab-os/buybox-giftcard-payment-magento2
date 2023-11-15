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

namespace BuyBox\Payment\Model;

use BuyBox\Payment\Gateway\Config\Config;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Payment\Model\Method\Logger as PaymentLogger;
use Psr\Log\LoggerInterface;

class RestClient
{
    public const HTTP_METHOD_POST = 'POST';
    public const RESPONSE_SUCCESS = 'Success';
    public const RESPONSE_KEY_FAILURE = 'Failure';
    public const RESPONSE_KEY_ACK = 'ACK';
    public const RESPONSE_KEY_SHORT_MESSAGE = 'L_SHORTMESSAGE0';
    public const RESPONSE_KEY_LONG_MESSAGE = 'L_LONGMESSAGE0';
    public const RESPONSE_KEY_ERROR_CODE = 'L_ERRORCODE0';

    public const KEY_METHOD = 'METHOD';
    public const KEY_PAYMENT_ACTION = 'PAYMENTACTION';
    public const KEY_AMOUNT = 'AMT';
    public const KEY_CURRENCY_CODE = 'CURRENCYCODE';
    public const KEY_TOKEN = 'TOKEN';
    public const KEY_TRANSACTION_ID = 'TRANSACTIONID';
    public const KEY_AUTHORIZATION_ID = 'AUTHORIZATIONID';
    public const KEY_PAYER_ID = 'PAYERID';
    public const KEY_REFUND_TRANSACTION_ID = 'REFUNDTRANSACTIONID';
    public const KEY_INV_NUM = 'INVNUM';

    public const KEY_COMPLETE_TYPE = 'COMPLETETYPE';
    public const KEY_REFUND_TYPE = 'REFUNDTYPE';

    public const KEY_RETURN_URL = 'RETURNURL';
    public const KEY_CANCEL_URL = 'CANCELURL';

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var PaymentLogger
     */
    private PaymentLogger $paymentLogger;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(Curl $curl, Config $config, PaymentLogger $paymentLogger, LoggerInterface $logger)
    {
        $this->curl = $curl;
        $this->config = $config;
        $this->paymentLogger = $paymentLogger;
        $this->logger = $logger;
    }

    /**
     * Call API.
     *
     * @throws LocalizedException
     */
    public function callApi(string $apiEndPoint, array $params, string $method = 'POST'): array
    {
        $response = [];
        $this->curl->setConfig(['timeout' => 30]);
        $this->curl->write(
            $method,
            $apiEndPoint,
            '1.1',
            [],
            http_build_query($params)
        );

        $this->paymentLogger->debug(['buybox_request' => $params], ['PWD', 'SIGNATURE'], $this->config->getDebug());

        try {
            $result = $this->curl->read();
            $result = preg_split('/^\r?$/m', $result, 2);
            $result = trim($result[1]);
            parse_str($result, $response);
            $this->paymentLogger->debug(['buybox_response' => $response], [], $this->config->getDebug());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), (array) $e);
        }

        $this->curl->close();

        if (
            !is_array($response) || empty($response) || !isset($response[self::RESPONSE_KEY_ACK])
            || $response[self::RESPONSE_KEY_ACK] == self::RESPONSE_KEY_FAILURE
        ) {
            if (isset($response[self::RESPONSE_KEY_LONG_MESSAGE])) {
                throw new LocalizedException(__($response[self::RESPONSE_KEY_LONG_MESSAGE]));
            }

            throw new LocalizedException(__('Can\'t connect to payment API! please try again...'));
        }

        return $response;
    }
}

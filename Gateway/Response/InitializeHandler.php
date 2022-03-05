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

namespace BuyBox\Payment\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\RestClient;

class InitializeHandler implements HandlerInterface
{
    /**
     * @var Config $config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Handle.
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var $payment Payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $payment
            ->setAmountPaid(0)
            ->setBaseAmountAuthorized($order->getBaseTotalDue())
            ->setAmountAuthorized($order->getTotalDue())
            ->setAdditionalInformation('token', $response[RestClient::KEY_TOKEN])
            ->setAdditionalInformation('payment_type', $this->config->getPaymentAction());
    }
}

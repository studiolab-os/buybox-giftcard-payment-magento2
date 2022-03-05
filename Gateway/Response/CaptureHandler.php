<?php

/**
 * BuyBox payment module for Magento
 *
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0.
 *
 * @author    Studiolab <contact@studiolab.fr>
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      https://www.buybox.net/
 */

namespace BuyBox\Payment\Gateway\Response;

use BuyBox\Payment\Model\RestClient;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class CaptureHandler implements HandlerInterface
{
    /**
     * Handle.
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $payment->setAmountPaid($order->getGrandTotalAmount());
        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->setLastTransId($response[RestClient::KEY_TRANSACTION_ID]);
        $payment->setAdditionalInformation('buybox_data', $response);
        $payment->setIsTransactionClosed(true);
    }
}

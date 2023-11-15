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

namespace BuyBox\Payment\Gateway\Response;

use BuyBox\Payment\Model\RestClient;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CaptureHandler implements HandlerInterface
{
    /**
     * Handle.
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        /** @var Payment $payment */
        $payment->setAmountPaid($order->getGrandTotalAmount());
        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->setLastTransId($response[RestClient::KEY_TRANSACTION_ID]);
        $payment->setAdditionalInformation(['buybox_data' => $response]);
        $payment->setIsTransactionClosed(true);
    }
}

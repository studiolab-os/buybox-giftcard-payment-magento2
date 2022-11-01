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

namespace BuyBox\Payment\Gateway\Request;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\RestClient;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundBuilder implements BuilderInterface
{
    /**
     * Builds Refund request.
     */
    public function build(array $buildSubject): array
    {
        $paymentDo = SubjectReader::readPayment($buildSubject);
        $amount = SubjectReader::readAmount($buildSubject);
        $payment = $paymentDo->getPayment();
        $order = $paymentDo->getOrder();
        $buybox_data = $payment->getAdditionalInformation('buybox_data');

        return [
            RestClient::KEY_METHOD         => Config::METHOD_REFUND_TRANSACTION,
            RestClient::KEY_TRANSACTION_ID => $buybox_data[RestClient::KEY_TRANSACTION_ID],
            RestClient::KEY_AMOUNT         => $amount,
            RestClient::KEY_REFUND_TYPE    => $this->getRefundType($order, $amount)
        ];
    }

    /**
     * Get refund type.
     */
    private function getRefundType(OrderAdapterInterface $order, float $amount): string
    {
        return $amount == $order->getGrandTotalAmount() ? Config::REFUND_TYPE_FULL : Config::REFUND_TYPE_PARTIAL;
    }
}

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

namespace BuyBox\Payment\Gateway\Request;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\RestClient;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundBuilder implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds Refund request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDo = SubjectReader::readPayment($buildSubject);
        $amount = SubjectReader::readAmount($buildSubject);
        $payment = $paymentDo->getPayment();
        $order = $paymentDo->getOrder();
        $buybox_data = $payment->getAdditionalInformation('buybox_data');

        return [
            RestClient::KEY_METHOD => Config::METHOD_REFUND_TRANSACTION,
            RestClient::KEY_TRANSACTION_ID => $buybox_data[RestClient::KEY_TRANSACTION_ID],
            RestClient::KEY_AMOUNT => $amount,
            RestClient::KEY_REFUND_TYPE => ($amount == $order->getGrandTotalAmount()) ? 'Full' : 'Partial'
        ];
    }
}

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

use BuyBox\Payment\Model\RestClient;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilderInterface;

class RefundHandler implements HandlerInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var TransactionBuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param TransactionBuilderInterface $transactionBuilder
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $paymentRepository,
        TransactionBuilderInterface $transactionBuilder,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionRepository = $transactionRepository;
    }

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
        $order = $this->orderRepository->get($paymentDO->getOrder()->getId());

        $transaction = $this->transactionBuilder
            ->setPayment($order->getPayment())
            ->setOrder($order)
            ->setTransactionId($response[RestClient::KEY_REFUND_TRANSACTION_ID])
            ->setAdditionalInformation(['buybox_data' => $response])
            ->setFailSafe(true)
            ->build(TransactionInterface::TYPE_REFUND);

        $this->transactionRepository->save($transaction);
    }
}

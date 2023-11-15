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
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilderInterface;

class RefundHandler implements HandlerInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var TransactionBuilderInterface
     */
    private TransactionBuilderInterface $transactionBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TransactionBuilderInterface $transactionBuilder,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Handle.
     */
    public function handle(array $handlingSubject, array $response): void
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

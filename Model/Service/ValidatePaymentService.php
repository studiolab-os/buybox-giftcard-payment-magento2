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

namespace BuyBox\Payment\Model\Service;

use BuyBox\Payment\Model\BuyBoxPayment;
use BuyBox\Payment\Model\RestClient;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

class ValidatePaymentService
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
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $paymentRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param BuilderInterface $transactionBuilder
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $paymentRepository,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $transactionBuilder,
        OrderManagementInterface $orderManagement
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Execute.
     *
     * @param Order $order
     * @param array $paymentData
     * @param string $transactionType
     * @return void
     * @throws LocalizedException
     */
    public function execute(Order $order, array $paymentData, string $transactionType)
    {
        try {

            if ($transactionType == TransactionInterface::TYPE_CAPTURE) {
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus(Order::STATE_PROCESSING);
            } else {
                $order->setState(BuyBoxPayment::ORDER_STATUS_AUTHORIZED_CODE);
                $order->setStatus(BuyBoxPayment::ORDER_STATE_AUTHORIZED_CODE);
            }

            $this->orderRepository->save($order);

            // get payment object from order object
            $payment = $order->getPayment();
            $payment->setLastTransId(
                $paymentData[RestClient::KEY_TRANSACTION_ID]
            )->setTransactionId(
                $paymentData[RestClient::KEY_TRANSACTION_ID]
            )->setAdditionalInformation(
                'buybox_data',
                $paymentData
            )->setIsTransactionClosed(
                ($transactionType == TransactionInterface::TYPE_CAPTURE)
            )->setIsTransactionPending(
                !($transactionType == TransactionInterface::TYPE_CAPTURE)
            )->setParentTransactionId(
                null
            );


            $transaction = $this->createTransaction($order, $paymentData, $transactionType);

            $formattedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );

            $message = __(
                'The %1 amount is %2.',
                $transactionType == TransactionInterface::TYPE_CAPTURE ? 'captured' : 'authorized',
                $formattedPrice
            );

            $payment->addTransactionCommentsToOrder($transaction, $message);

            $payment->setAdditionalInformation('buybox_data', $paymentData);
            $this->paymentRepository->save($payment);

            $order->setCanSendNewEmailFlag(true);
            $this->orderRepository->save($order);

            $this->orderManagement->notify($order->getId());
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Create Transaction.
     *
     * @param Order $order
     * @param array $paymentData
     * @param string $transactionType
     * @return TransactionInterface
     */
    private function createTransaction(Order $order, array $paymentData, string $transactionType): TransactionInterface
    {
        $payment = $order->getPayment();

        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData[RestClient::KEY_TRANSACTION_ID])
            ->addAdditionalInformation(
                'buybox_data',
                $paymentData
            )->setFailSafe(true)
            ->build($transactionType);

        $this->transactionRepository->save($transaction);

        return $transaction;
    }
}

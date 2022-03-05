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

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

class VoidHandler implements HandlerInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->transactionRepository = $transactionRepository;
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
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        $authorizationTransaction = $this->getTransactionTypeAuth($payment, $order);

        $payment->setParentTransactionId($authorizationTransaction->getTxnId());
        $payment->setTransactionId($authorizationTransaction->getTxnId() . '-void');

        $payment->setAmountPaid(0)
            ->setAmountCanceled($order->getGrandTotalAmount())
            ->setBaseAmountAuthorized(0)
            ->setAmountAuthorized(0);
    }

    /**
     * Get transaction type auth.
     *
     * @param InfoInterface $payment
     * @param OrderAdapterInterface $order
     * @return false|TransactionInterface
     * @throws InputException
     */
    protected function getTransactionTypeAuth(InfoInterface $payment, OrderAdapterInterface $order)
    {
        return $this->transactionRepository->getByTransactionType(
            TransactionInterface::TYPE_AUTH,
            $payment->getId(),
            $order->getId()
        );
    }
}

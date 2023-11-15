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

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;

class VoidHandler implements HandlerInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Handle.
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $authorization = $this->getTransactionTypeAuth($payment);

        /** @var Payment $payment */
        $payment->setParentTransactionId($authorization->getTxnId());
        $payment->setTransactionId($authorization->getTxnId() . '-void');

        $payment
            ->setIsTransactionClosed(true)
            ->setShouldCloseParentTransaction(true);
    }

    /**
     * Get transaction type auth.
     *
     * @throws InputException
     */
    protected function getTransactionTypeAuth(InfoInterface $payment): ?TransactionInterface
    {
        return $this->transactionRepository->getByTransactionType(
            TransactionInterface::TYPE_AUTH,
            $payment->getId()
        );
    }
}

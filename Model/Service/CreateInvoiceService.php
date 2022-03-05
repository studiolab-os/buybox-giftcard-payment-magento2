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

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;

class CreateInvoiceService
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var Transaction
     */
    protected $transaction;


    /**
     * CreateInvoiceService constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Execute.
     *
     * @param Order $order
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setTransactionId($order->getPayment()->getLastTransId())
                ->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->getOrder()->setCustomerNoteNotify(true);
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->register();

            $transactionSave = ObjectManager::getInstance()->create(
                Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $order
            );

            $transactionSave->save();
            $this->invoiceSender->send($invoice);
        }
    }
}

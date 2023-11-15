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

namespace BuyBox\Payment\Controller\Payment;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\BuyBoxPayment;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ReturnAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var BuyBoxPayment
     */
    private BuyBoxPayment $buyBoxPayment;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $messageManager;

    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * ReturnAction constructor.
     */
    public function __construct(
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        BuyBoxPayment $buyBoxPayment,
        MessageManagerInterface $messageManager,
        ResultRedirectFactory $resultRedirectFactory
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->buyBoxPayment = $buyBoxPayment;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Execute.
     */
    public function execute(): ResultInterface
    {
        $redirect = $this->resultRedirectFactory->create();
        $params = $this->request->getParams();

        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if (!$order->getEntityId()) {
                throw new LocalizedException(__('Cannot get order information from session.'));
            }

            $payment = $order->getPayment();
            if (!$payment) {
                throw new LocalizedException(
                    __('Cannot get payment information from order %s', $order->getIncrementId())
                );
            }

            if (!$this->validateParams($payment, $params)) {
                $this->restoreQuote($order);
                throw new LocalizedException(__('Error getting token information from session.'));
            }

            $order->addCommentToStatusHistory(
                __('Customer is back from BuyBox payment page.')->render(),
                Order::STATE_PENDING_PAYMENT
            );

            $order->setCanSendNewEmailFlag(true);

            $this->orderRepository->save($order);

            $this->buyBoxPayment->process($order, $params);

            return $redirect->setPath('checkout/onepage/success/');
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $redirect->setPath('checkout/cart');
    }

    /**
     * Validate params.
     */
    private function validateParams(OrderPaymentInterface $payment, array $params): bool
    {
        if (
            !$payment->getAdditionalInformation(Config::KEY_TOKEN) || !isset($params[Config::KEY_TOKEN])
            || !isset($params[Config::KEY_PAYER_ID]) || !$params[Config::KEY_PAYER_ID]
            || $payment->getAdditionalInformation(Config::KEY_TOKEN) != $params[Config::KEY_TOKEN]
        ) {
            return false;
        }

        return true;
    }

    /**
     * Restore quote.
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function restoreQuote(OrderInterface $order): void
    {
        try {
            $quote = $this->cartRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Cannot get order information from session.'));
        }

        $quote->setIsActive(true);
        $this->cartRepository->save($quote);
        $this->checkoutSession->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());
    }
}

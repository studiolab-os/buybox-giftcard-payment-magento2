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

namespace BuyBox\Payment\Controller\Payment;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\BuyBoxPayment;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ReturnAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var BuyBoxPayment
     */
    private $buyBoxPayment;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param BuyBoxPayment $buyBoxPayment
     * @param Config $config
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        BuyBoxPayment $buyBoxPayment,
        Config $config
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->buyBoxPayment = $buyBoxPayment;
        $this->config = $config;

        $this->request = $context->getRequest();
    }

    /**
     * Execute.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $redirect = $this->resultRedirectFactory->create();
        $params = $this->request->getParams();

        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if (!$order || !$order->getEntityId()) {
                throw new LocalizedException(__('Cannot get order information from session'));
            }

            $payment = $order->getPayment();
            if (!$payment) {
                throw new LocalizedException(
                    __('Cannot get payment information from order %s', $order->getIncrementId())
                );
            }

            if (
                null == $payment->getAdditionalInformation('token')
                || !$params['token'] || !$params['PayerID']
                || $payment->getAdditionalInformation('token') != $params['token']
            ) {
                $this->restoreQuote($order);
                throw new LocalizedException(__('Error getting token information from session'));
            }

            $comment = __('Customer is back from BuyBox payment page.');

            if (
                method_exists($order, 'addCommentToStatusHistory')
                && is_callable([$order, 'addCommentToStatusHistory'])
            ) {
                $order->addCommentToStatusHistory(
                    $comment,
                    Order::STATE_PENDING_PAYMENT
                );
            } else {
                $order->addStatusHistoryComment(
                    $comment,
                    Order::STATE_PENDING_PAYMENT
                );
            }

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
     * @throws NoSuchEntityException
     */
    private function restoreQuote($order)
    {
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(true);
        $this->quoteRepository->save($quote);
        $this->checkoutSession->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());
    }
}

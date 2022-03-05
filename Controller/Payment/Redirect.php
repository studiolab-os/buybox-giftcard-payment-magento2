<?php

/**
 * BuyBox payment module for Magento
 *
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0.
 *
 * @author    Studiolab <contact@studiolab.fr>
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      https://www.buybox.net/
 */

namespace BuyBox\Payment\Controller\Payment;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Helper\Url;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        Url $url
    ) {
        parent::__construct($context);
        $this->url = $url;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $redirect = $this->resultRedirectFactory->create();

        try {
            $order = $this->checkoutSession->getLastRealOrder();

            if (!$order) {
                throw new LocalizedException(__('Cannot get order information from session'));
            }

            $payment = $order->getPayment();
            if (!$payment) {
                throw new LocalizedException(__('Cannot get payment information from session'));
            }

            if (null == $payment->getAdditionalInformation('token')) {
                throw new LocalizedException(__('Error getting token information from session'));
            }

            $comment = __('Successfully created Payment Token. customer is Redirected to the payment interface');

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

            $this->orderRepository->save($order);

            return $redirect->setUrl(
                $this->url->getRedirectUrl((string)$payment->getAdditionalInformation('token'))
            );
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $redirect->setPath('checkout/cart');
    }
}

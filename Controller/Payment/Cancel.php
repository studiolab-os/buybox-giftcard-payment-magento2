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
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Cancel extends \Magento\Framework\App\Action\Action
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
     * @var Config
     */
    private $config;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Config $config,
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
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

            $order->setState(Order::STATE_CANCELED);
            $order->setStatus(Order::STATE_CANCELED);
            if (
                method_exists($order, 'addCommentToStatusHistory')
                && is_callable([$order, 'addCommentToStatusHistory'])
            ) {
                $order->addCommentToStatusHistory(__('Order Canceled By customer.'), Order::STATE_CANCELED);
            } else {
                $order->addStatusHistoryComment(__('Order Canceled By customer.'), Order::STATE_CANCELED);
            }

            $this->orderRepository->save($order);

            return $redirect->setPath('checkout/onepage/failure');
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $redirect->setPath('checkout/cart');
    }
}

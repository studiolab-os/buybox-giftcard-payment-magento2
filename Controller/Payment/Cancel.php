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

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Cancel implements HttpPostActionInterface, HttpGetActionInterface
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultRedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * Cancel constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $messageManager
     * @param ResultRedirectFactory $resultRedirectFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $messageManager,
        ResultRedirectFactory $resultRedirectFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Execute.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $redirect = $this->resultRedirectFactory->create();

        try {
            $order = $this->checkoutSession->getLastRealOrder();

            if (!$order) {
                throw new LocalizedException(__('Cannot get order information from session'));
            }

            $order->setState(Order::STATE_CANCELED);
            $order->setStatus(Order::STATE_CANCELED);
            $order->addCommentToStatusHistory(__('Order Canceled By customer.'), Order::STATE_CANCELED);

            $this->orderRepository->save($order);

            $this->messageManager->addNoticeMessage('You cancel the order.');

            return $redirect->setPath('checkout/onepage/failure');
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $redirect->setPath('checkout/cart');
    }
}

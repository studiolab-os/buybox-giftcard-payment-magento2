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

declare(strict_types=1);

namespace BuyBox\Payment\Controller\Payment;

use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Helper\Url;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Redirect implements HttpPostActionInterface, HttpGetActionInterface
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
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultRedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var Url
     */
    private $url;

    /**
     * Redirect constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param MessageManagerInterface $messageManager
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Url $url
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        MessageManagerInterface $messageManager,
        ResultRedirectFactory $resultRedirectFactory,
        Url $url
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->url = $url;
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

            $payment = $order->getPayment();
            if (!$payment) {
                throw new LocalizedException(__('Cannot get payment information from session'));
            }

            $paymentInformation = $payment->getAdditionalInformation();

            if (
                !isset($paymentInformation[Config::KEY_TOKEN])
                || $paymentInformation[Config::KEY_TOKEN] == null
            ) {
                throw new LocalizedException(__('Error getting token information from session'));
            }

            $order->addCommentToStatusHistory(
                __('Successfully created Payment Token. customer is Redirected to the payment interface'),
                Order::STATE_PENDING_PAYMENT
            );

            $this->orderRepository->save($order);

            return $redirect->setUrl(
                $this->url->getRedirectUrl($paymentInformation[Config::KEY_TOKEN])
            );
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $redirect->setPath('checkout/cart');
    }
}

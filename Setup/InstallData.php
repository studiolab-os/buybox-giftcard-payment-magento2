<?php

namespace BuyBox\Payment\Setup;

use BuyBox\Payment\Model\BuyBoxPayment;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Install.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addNewOrderProcessingStatus();
        $this->addNewOrderStateAndStatus();
    }

    /**
     * Create new order processing status and assign it to the existent state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderProcessingStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();

        /** @var Status $status */
        $status = $this->statusFactory->create();

        $status->setData([
            'status' => BuyBoxPayment::ORDER_STATUS_AUTHORIZED_CODE,
            'label' => BuyBoxPayment::ORDER_STATUS_AUTHORIZED_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }

        $status->assignState(Order::STATE_PROCESSING, false, true);
    }

    /**
     * Create new custom order status and assign it to the new custom order state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderStateAndStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();

        /** @var Status $status */
        $status = $this->statusFactory->create();

        $status->setData([
            'status' => BuyBoxPayment::ORDER_STATE_AUTHORIZED_CODE,
            'label' => BuyBoxPayment::ORDER_STATE_AUTHORIZED_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }

        $status->assignState(BuyBoxPayment::ORDER_STATUS_AUTHORIZED_CODE, true, true);
    }
}
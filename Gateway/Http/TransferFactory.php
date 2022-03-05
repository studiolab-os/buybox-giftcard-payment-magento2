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

namespace BuyBox\Payment\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use BuyBox\Payment\Gateway\Request\MockDataRequest;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param TransferBuilder $transferBuilder
     * @param Logger $logger
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Logger $logger
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->logger = $logger;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request): TransferInterface
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}

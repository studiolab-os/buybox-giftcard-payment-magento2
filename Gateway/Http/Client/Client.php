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

namespace BuyBox\Payment\Gateway\Http\Client;

use Exception;
use InvalidArgumentException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use BuyBox\Payment\Gateway\Config\Config;
use BuyBox\Payment\Model\RestClient;
use Zend_Http_Client;

class Client implements ClientInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RestClient
     */
    private $restClient;


    /**
     * @param Config $config
     * @param RestClient $restClient
     */
    public function __construct(Config $config, RestClient $restClient)
    {
        $this->config = $config;
        $this->restClient = $restClient;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        try {
            $response = $this->restClient->callApi(
                $this->config->getApiEndpoint(),
                $transferObject->getBody(),
                Zend_Http_Client::POST
            );
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $response;
    }
}

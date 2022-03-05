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

namespace BuyBox\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use BuyBox\Payment\Gateway\Config\Config;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{

    const KEY_REDIRECT_URL = 'redirect_url';
    const KEY_CODE = 'code';

    /**
     * @var UrlInterface
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }


    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                Config::CODE => [
                    self::KEY_CODE => Config::CODE,
                    self::KEY_REDIRECT_URL => $this->config->getUrl(Config::ACTION_REDIRECT),
                ]
            ]
        ];
    }
}

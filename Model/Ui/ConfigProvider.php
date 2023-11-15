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

namespace BuyBox\Payment\Model\Ui;

use BuyBox\Payment\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    public const KEY_REDIRECT_URL = 'redirect_url';
    public const KEY_CODE = 'code';

    /**
     * @var Config
     */
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration.
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

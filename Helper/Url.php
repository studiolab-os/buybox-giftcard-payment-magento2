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

namespace BuyBox\Payment\Helper;

use BuyBox\Payment\Gateway\Config\Config;
use Magento\Framework\Locale\Resolver;

class Url
{
    public const REDIRECT_URL = 'https://%s/secure/payment_login.php';
    public const REDIRECT_URL_PARAMS = [
        'useraction' => 'commit',
        'token'      => '%s',
        'lang'       => '%s'
    ];

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Resolver $localeResolver, Config $config)
    {
        $this->localeResolver = $localeResolver;
        $this->config = $config;
    }

    /**
     * Get Redirect Url.
     */
    public function getRedirectUrl(string $token): string
    {
        $params = [];
        foreach (self::REDIRECT_URL_PARAMS as $key => $value) {
            $params[] = sprintf('%s=%s', $key, $value);
        }

        return sprintf(
            self::REDIRECT_URL . '?' . implode('&', $params),
            $this->config->getServiceDomain(),
            $token,
            $this->localeResolver->getLocale()
        );
    }
}

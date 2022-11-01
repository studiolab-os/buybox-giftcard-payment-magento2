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

namespace BuyBox\Payment\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

class Info extends ConfigurableInfo
{
    /**
     * Returns label.
     *
     * @param string $field
     */
    protected function getLabel($field): Phrase
    {
        return __($field);
    }

    /**
     * Returns value view.
     *
     * @param string $field
     * @param string $value
     */
    protected function getValueView($field, $value): Phrase
    {
        return __(parent::getValueView($field, $value));
    }
}

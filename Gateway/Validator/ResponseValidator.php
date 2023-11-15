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

namespace BuyBox\Payment\Gateway\Validator;

use BuyBox\Payment\Model\RestClient;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class ResponseValidator extends AbstractValidator
{
    public const RESULT_ACK = 'ACK';

    /**
     * Performs validation of result code.
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = SubjectReader::readResponse($validationSubject);

        if ($this->isSuccessfulResponse($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__($response[RestClient::RESPONSE_KEY_SHORT_MESSAGE] ?? 'Can\'t get response from API...')]
            );
        }
    }

    /**
     * Is Successful Response.
     */
    private function isSuccessfulResponse(array $response): bool
    {
        return isset($response[RestClient::RESPONSE_KEY_ACK])
            && $response[RestClient::RESPONSE_KEY_ACK] !== RestClient::RESPONSE_KEY_FAILURE;
    }
}

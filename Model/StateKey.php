<?php
/**
 * Copyright 2020 Vipps
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Vipps\Login\Model;

use Magento\Framework\Math\Random;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class StateKey
 * @package Vipps\Login\Model
 * @api
 */
class StateKey
{
    /**
     * @var string
     */
    const DATA_KEY_STATE = 'vipps_login_url_state';

    /**
     * @var Random
     */
    private $mathRand;

    /**
     * StateKey constructor.
     *
     * @param Random $mathRand
     */
    public function __construct(
        Random $mathRand
    ) {
        $this->mathRand = $mathRand;
    }

    /**
     * Method to generate stateKey for vipps/login requests.
     * This stateKey uses for validation on redirect action from vipps/login.
     *
     * @return string
     * @throws LocalizedException
     */
    public function generate()
    {
        return $this->mathRand->getUniqueHash();
    }
}

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
namespace Vipps\Login\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Interface UserInfoInterface
 * @package Vipps\Login\Api\Data
 */
interface UserInfoInterface extends CustomAttributesDataInterface
{
    /**
     * @var string
     */
    const BIRTHDATE = 'birthdate';

    /**
     * @var string
     */
    const EMAIL = 'email';

    /**
     * @var string
     */
    const FAMILY_NAME = 'family_name';

    /**
     * @var string
     */
    const GIVEN_NAME = 'given_name';

    /**
     * @var string
     */
    const NAME = 'name';

    /**
     * @var string
     */
    const PHONE_NUMBER = 'phone_number';

    /**
     * @var string
     */
    const ADDRESS = 'address';

    /**
     * @return mixed
     */
    public function getBirthdate();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setBirthdate($value);

    /**
     * @return mixed
     */
    public function getEmail();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setEmail($value);

    /**
     * @return mixed
     */
    public function getFamilyName();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setFamilyName($value);

    /**
     * @return mixed
     */
    public function getGivenName();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setGivenName($value);

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setName($value);

    /**
     * @return mixed
     */
    public function getPhoneNumber();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setPhoneNumber($value);

    /**
     * @return mixed|[]
     */
    public function getAddress();

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setAddress($value);
}

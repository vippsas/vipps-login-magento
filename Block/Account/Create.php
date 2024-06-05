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

namespace Vipps\Login\Block\Account;

use Vipps\Login\Block\Account\Template as AccountTemplate;
use Magento\Framework\View\Element\Template;
use Vipps\Login\Api\Block\ClassPoolInterface;
use Vipps\Login\Model\VippsSession;

/**
 * Class Create
 * @package Vipps\Login\Block\Account
 */
class Create extends AccountTemplate
{
    /**
     * @var VippsSession
     */
    private $vippsSession;

    /**
     * Create constructor.
     *
     * @param Template\Context $context
     * @param VippsSession $vippsSession
     * @param array $data
     */
    public function __construct(
        Template\Context   $context,
        VippsSession       $vippsSession,
        ClassPoolInterface $classPool,
        array              $data = []
    ) {
        parent::__construct($classPool, $context, $data);
        $this->vippsSession = $vippsSession;
    }

    /**
     * @return bool
     */
    public function isVippsLoggedIn(): bool
    {
        return $this->vippsSession->isLoggedIn();
    }
}

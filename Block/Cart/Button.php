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

namespace Vipps\Login\Block\Cart;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session;
use Vipps\Login\Model\ConfigInterface;

/**
 * Class Button
 * @package Vipps\Login\Block\Cart
 */
class Button extends Template
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * Link constructor.
     *
     * @param ConfigInterface $config
     * @param SessionManagerInterface $customerSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        SessionManagerInterface $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->customerSession = $customerSession;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        //If Vipps payment method express checkout is enabled and this method
        // shows express checkout button on cart page then
        // we need to hide vipps/login button
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $cartDisplay = $this->config->getValue('payment/vipps/checkout_cart_display');
        $expressCheckout = $this->config->getValue('payment/vipps/express_checkout');
        if (!$isLoggedIn && (!$cartDisplay || !$expressCheckout)) {
            return parent::_toHtml();
        }

        return '';
    }
}

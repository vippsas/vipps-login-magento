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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class RedirectPathResolver
 * @package Vipps\Login\Model
 */
class RedirectUrlResolver
{
    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $redirectUrl = null;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * RedirectUrlResolver constructor.
     *
     * @param SessionManagerInterface $customerSession
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
    }

    /**
     * Retrieve redirect back url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        if (!empty($this->redirectUrl)) {
            return $this->redirectUrl;
        }

        $this->redirectUrl = $this->customerSession->getVippsRedirectUrl();

        if (strpos($this->redirectUrl, 'checkout/cart') !== false) {
            $this->redirectUrl = $this->url->getUrl('checkout');
        } elseif ($this->scopeConfig->getValue('customer/startup/redirect_dashboard')) {
            $this->redirectUrl = $this->url->getUrl('customer/account');
        }

        return $this->redirectUrl;
    }
}

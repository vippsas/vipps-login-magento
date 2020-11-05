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

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

/**
 * Class AccountBase
 * @package Vipps\Login\Controller\Login\Account
 */
abstract class AccountBase implements ActionInterface
{
    /**
     * Customer session
     *
     * @var SessionManagerInterface|Session
     */
    protected $customerSession;

    /**
     * Customer session
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AccountBase constructor.
     *
     * @param SessionManagerInterface $customerSession
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Check customer authentication for some actions.
     *
     * @return bool
     */
    public function canProcess(): bool
    {
        return $this->customerSession->authenticate();
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest(): RequestInterface
    {
        return $this->request;
    }
}

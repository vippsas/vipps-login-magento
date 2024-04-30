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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\Page;
use Psr\Log\LoggerInterface;
use Vipps\Login\Model\ConfigInterface;

/**
 * Class Manage
 * @package Vipps\Login\Controller\Login
 */
class Manage extends AccountBase
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    private ConfigInterface $config;

    /**
     * Manage constructor.
     *
     * @param SessionManagerInterface $customerSession
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        SessionManagerInterface $customerSession,
        ConfigInterface         $config,
        RequestInterface        $request,
        LoggerInterface         $logger,
        ResultFactory           $resultFactory
    ) {
        parent::__construct($customerSession, $request, $logger);
        $this->resultFactory = $resultFactory;
        $this->config = $config;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Log in with %1', __($this->config->getTitle())));

        return $resultPage;
    }
}

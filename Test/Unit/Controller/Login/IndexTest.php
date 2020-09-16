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

namespace Vipps\Login\Test\Unit\Controller\Login;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Controller\Login\Index;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\StateKey;

/**
 * Class IndexTest
 * @package Vipps\Login\Test\Unit\Controller\Login
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $action;

    /**
     * @var SessionManagerInterface
     */
    private $customerSession;

    /**
     * @var ApiEndpointsInterface|MockObject
     */
    private $apiEndPoints;

    /**
     * @var ConfigInterface|MockObject
     */
    private $config;

    /**
     * @var StateKey|MockObject
     */
    private $stateKey;

    /**
     * @var UrlInterface|MockObject
     */
    private $url;

    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Redirect|MockObject
     */
    private $redirect;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirect;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * Sets up the fixtures
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getResultRedirectFactory', 'getRedirect', 'getMessageManager'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setVippsRedirectUrl', 'setData'])
            ->getMockForAbstractClass();

        $this->apiEndPoints = $this->getMockBuilder(ApiEndpointsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthorizationEndpoint'])
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLoginClientId'])
            ->getMockForAbstractClass();

        $this->stateKey = $this->getMockBuilder(StateKey::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        $this->url = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical'])
            ->getMockForAbstractClass();

        $this->redirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $context->expects(self::once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);

        $this->redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRefererUrl'])
            ->getMock();
        $context->expects(self::once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUrl'])
            ->getMock();
        $this->redirectFactory->expects(self::once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['addErrorMessage'])
            ->getMockForAbstractClass();
        $context->expects(self::once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(Index::class, [
            'context' => $context,
            'customerSession' => $this->customerSession,
            'apiEndpoints' => $this->apiEndPoints,
            'config' => $this->config,
            'stateKey' => $this->stateKey,
            'url' => $this->url,
            'logger' => $this->logger,
        ]);
    }

    /**
     * Test case checks if required parameter Client Id
     * is configured.
     */
    public function testMissingParameterException()
    {
        $refererUrl = 'customer/account/login';
        $this->redirect->expects(self::once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $clientId = null;
        $this->config->expects(self::once())
            ->method('getLoginClientId')
            ->willReturn($clientId);

        $errorMessage = 'Invalid module configuration. Please, contact store administrator.';

        $this->messageManager->expects(self::once())
            ->method('addErrorMessage')
            ->with($errorMessage)
            ->willReturnSelf();
        $this->logger->expects(self::once())
            ->method('critical')
            ->with($errorMessage)
            ->willReturnSelf();
        $this->resultRedirect->expects(self::once())
            ->method('setUrl')
            ->with($refererUrl)
            ->willReturnSelf();

        self::assertEquals($this->action->execute(), $this->resultRedirect);
    }

    /**
     * Test case to check a correct error handling
     * when api Endpoint is not configured.
     */
    public function testMissingEndPointsException()
    {
        $refererUrl = 'customer/account/login';
        $this->redirect->expects(static::once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $clientId = 'clientId';
        $this->config->expects(static::once())
            ->method('getLoginClientId')
            ->willReturn($clientId);

        $state = 'stateKey';
        $this->stateKey->expects(static::once())
            ->method('generate')
            ->willReturn($state);

        $this->customerSession->expects(static::once())
            ->method('setData')
            ->with('vipps_login_url_state', $state)
            ->willReturnSelf();

        $url = 'https://test.test.com/vipps/login/redirect';
        $this->url->expects(static::once())
            ->method('getUrl')
            ->with('vipps/login/redirect')
            ->willReturn($url);

        $errorMessage = 'An error occurred. Please, try again later.';
        $exception = new \Exception($errorMessage);
        $this->apiEndPoints->expects(static::once())
            ->method('getAuthorizationEndpoint')
            ->willThrowException($exception);


        $this->messageManager->expects(static::once())
            ->method('addErrorMessage')
            ->with(__($errorMessage))
            ->willReturnSelf();

        $this->logger->expects(static::once())
            ->method('critical')
            ->with($errorMessage)
            ->willReturnSelf();

        $this->resultRedirect->expects(static::once())
            ->method('setUrl')
            ->with($refererUrl)
            ->willReturnSelf();
        self::assertEquals($this->action->execute(), $this->resultRedirect);
    }

    /**
     * Test case to check if configured openIDConnect provider
     * returns correct authorization URL.
     */
    public function testSuccess()
    {
        $refererUrl = 'customer/account/login';
        $this->redirect->expects(static::once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $clientId = 'clientId';
        $this->config->expects(static::once())
            ->method('getLoginClientId')
            ->willReturn($clientId);

        $state = 'stateKey';
        $this->stateKey->expects(static::once())
            ->method('generate')
            ->willReturn($state);

        $this->customerSession->expects(static::once())
            ->method('setData')
            ->with('vipps_login_url_state', $state)
            ->willReturnSelf();

        $url = 'https://test.test.com/vipps/login/redirect';
        $this->url->expects(static::once())
            ->method('getUrl')
            ->with('vipps/login/redirect')
            ->willReturn($url);

        $endPoint = 'endpoint/url';
        $this->apiEndPoints->expects(static::once())
            ->method('getAuthorizationEndpoint')
            ->willReturn($endPoint);

        $this->customerSession->expects(static::once())
            ->method('setVippsRedirectUrl')
            ->with($refererUrl)
            ->willReturnSelf();

        $this->resultRedirect->expects(static::once())
            ->method('setUrl')
            ->with('endpoint/url?client_id=clientId&response_type=code&scope=openid address name email phoneNumber&state=stateKey&redirect_uri=https://test.test.com/vipps/login/redirect')
            ->willReturnSelf();

        self::assertEquals($this->action->execute(), $this->resultRedirect);
    }
}

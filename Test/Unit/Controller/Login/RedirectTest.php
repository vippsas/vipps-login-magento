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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect as MagentoRedirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Vipps\Login\Controller\Login\Redirect;
use Vipps\Login\Controller\Login\Redirect\ActionsPool;
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Model\RedirectUrlResolver;

/**
 * Class IndexTest
 * @package Vaimo\OpenIDConnect\Test\Unit\Controller\Authorize
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends TestCase
{
    /**
     * @var Redirect
     */
    private $action;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var TokenCommand|MockObject
     */
    private $tokenCommand;

    /**
     * @var ActionsPool|MockObject
     */
    private $actionsPool;

    /**
     * @var RedirectUrlResolver|MockObject
     */
    private $redirectUrlResolver;

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
    private $resultRedirect;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

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
            ->setMethods(['getResultRedirectFactory', 'getRequest', 'getRedirect', 'getMessageManager'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionManager = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMockForAbstractClass();

        $this->tokenCommand = $this->getMockBuilder(TokenCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->actionsPool = $this->getMockBuilder(ActionsPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->redirectUrlResolver = $this->getMockBuilder(RedirectUrlResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRedirectUrl'])
            ->getMock();

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

        $this->resultRedirect = $this->getMockBuilder(MagentoRedirect::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setUrl'])
            ->getMock();
        $this->redirectFactory->expects(self::once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $context->expects(self::once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['addErrorMessage'])
            ->getMockForAbstractClass();
        $context->expects(self::once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(Redirect::class, [
            'context' => $context,
            'sessionManager' => $this->sessionManager,
            'tokenCommand' => $this->tokenCommand,
            'actionsPool' => $this->actionsPool,
            'redirectUrlResolver' => $this->redirectUrlResolver,
            'logger' => $this->logger,
        ]);
    }

    /**
     * Test case to check execute method
     *
     * @param $state
     * @param $storedState
     * @param $code
     * @param $error
     * @param $errorDescription
     *
     * @dataProvider dataProvider
     */
    public function testExecute($state, $storedState, $code, $error, $errorDescription)
    {
        $redirectUrl = 'redirect/url';
        $this->request->expects(static::any())
            ->method('getParam')
            ->will($this->returnValueMap([
                ['state', null, $state],
                ['code', null, $code],
                ['error', null, $error],
                ['errorDescription', null, $errorDescription],
            ]));

        if (empty($code) && $error) {
            $this->redirectUrlResolver->expects(static::once())
                ->method('getRedirectUrl')
                ->willReturn($redirectUrl);

            $this->messageManager->expects(self::once())
                ->method('addErrorMessage')
                ->willReturnSelf();

            $this->resultRedirect->expects(static::once())
                ->method('setUrl')
                ->with($redirectUrl)
                ->willReturnSelf();
        } else {
            $this->sessionManager->expects(static::once())
                ->method('getData')
                ->with('vipps_login_url_state')
                ->willReturn($storedState);
            if ($state !== $storedState) {
                $this->messageManager->expects(self::once())
                    ->method('addErrorMessage')
                    ->willReturnSelf();
                $this->logger->expects(self::once())
                    ->method('critical')
                    ->with('Invalid state key.')
                    ->willReturnSelf();
            } else {

                $token = [
                    'id_token' => 'id_token',
                    'id_token_payload' => 'id_token_payload',
                    'access_token' => 'access_token',
                ];
                $this->tokenCommand->expects(static::once())
                    ->method('execute')
                    ->with($code)
                    ->willReturn($token);
            }
        }

        $this->action->execute();
    }

    public function dataProvider()
    {
        return [
            //$state, $storedState, $code, $error, $errorDescription
            ['state', 'state', null, 'error', 'errorDescription'],
            ['state', 'state1', 'code', '', ''],
            ['state', 'state', 'code', '', ''],
        ];
    }
}

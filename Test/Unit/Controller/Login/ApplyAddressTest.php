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

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;
use Vipps\Login\Api\Data\VippsCustomerInterface;
use Vipps\Login\Api\VippsCustomerAddressRepositoryInterface;
use Vipps\Login\Api\VippsCustomerRepositoryInterface;
use Vipps\Login\Controller\Login\ApplyAddress;

/**
 * Covers the VIPPS-51 IDOR fix: the address id comes straight from a request param, so the
 * controller must verify the address belongs to the current customer before prefilling its PII,
 * and must not reject a legitimate owner due to int-vs-string id typing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplyAddressTest extends TestCase
{
    /** @var RedirectFactory|MockObject */
    private $resultRedirectFactory;

    /** @var Redirect|MockObject */
    private $resultRedirect;

    /** @var RequestInterface|MockObject */
    private $request;

    /** @var SessionManagerInterface|MockObject */
    private $customerSession;

    /** @var ManagerInterface|MockObject */
    private $messageManager;

    /** @var VippsCustomerAddressRepositoryInterface|MockObject */
    private $vippsCustomerAddressRepository;

    /** @var VippsCustomerRepositoryInterface|MockObject */
    private $vippsCustomerRepository;

    /** @var ApplyAddress */
    private $action;

    protected function setUp(): void
    {
        $this->resultRedirect = $this->createMock(Redirect::class);
        $this->resultRedirect->method('setPath')->willReturnSelf();

        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->resultRedirectFactory->method('create')->willReturn($this->resultRedirect);

        $this->request = $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass();

        $this->customerSession = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomer', 'setAddressFormData'])
            ->getMockForAbstractClass();

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataModel'])
            ->getMock();
        $customer->method('getDataModel')->willReturn($this->createMock(CustomerInterface::class));
        $this->customerSession->method('getCustomer')->willReturn($customer);

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)->getMockForAbstractClass();
        $this->vippsCustomerAddressRepository = $this->createMock(VippsCustomerAddressRepositoryInterface::class);
        $this->vippsCustomerRepository = $this->createMock(VippsCustomerRepositoryInterface::class);
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->action = $objectManager->getObject(ApplyAddress::class, [
            'resultRedirectFactory' => $this->resultRedirectFactory,
            'request' => $this->request,
            'logger' => $logger,
            'customerSession' => $this->customerSession,
            'messageManager' => $this->messageManager,
            'vippsCustomerAddressRepository' => $this->vippsCustomerAddressRepository,
            'vippsCustomerRepository' => $this->vippsCustomerRepository,
        ]);
    }

    /**
     * An address owned by a different vipps customer must be rejected: error message, redirect to the
     * address list, and crucially no PII written into the session form data.
     */
    public function testRejectsAddressOwnedByAnotherCustomer(): void
    {
        $this->request->method('getParam')->with('id', false)->willReturn('42');

        $vippsCustomer = $this->createMock(VippsCustomerInterface::class);
        $vippsCustomer->method('getEntityId')->willReturn(7);
        $this->vippsCustomerRepository->method('getByCustomer')->willReturn($vippsCustomer);

        $vippsAddress = $this->createMock(VippsCustomerAddressInterface::class);
        $vippsAddress->method('getVippsCustomerId')->willReturn('999');
        $this->vippsCustomerAddressRepository->method('getById')->with('42')->willReturn($vippsAddress);

        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->customerSession->expects($this->never())->method('setAddressFormData');
        $this->resultRedirect->expects($this->once())->method('setPath')->with('customer/address/index');

        $this->assertSame($this->resultRedirect, $this->action->execute());
    }

    /**
     * The legitimate owner must be accepted even when the two ids differ only by type (a string
     * '7' from the DB vs an int 7 on a freshly saved model) — the int cast guards that.
     */
    public function testAppliesOwnAddressWhenIdsMatchAcrossIntAndString(): void
    {
        $this->request->method('getParam')->with('id', false)->willReturn('42');

        $vippsCustomer = $this->createMock(VippsCustomerInterface::class);
        $vippsCustomer->method('getEntityId')->willReturn(7);
        $vippsCustomer->method('getTelephone')->willReturn('+4712345678');
        $this->vippsCustomerRepository->method('getByCustomer')->willReturn($vippsCustomer);

        $vippsAddress = $this->createMock(VippsCustomerAddressInterface::class);
        $vippsAddress->method('getVippsCustomerId')->willReturn('7');
        $vippsAddress->method('getPostalCode')->willReturn('0123');
        $vippsAddress->method('getRegion')->willReturn('Oslo');
        $vippsAddress->method('getCountry')->willReturn('NO');
        $vippsAddress->method('getStreetAddress')->willReturn('Karl Johans gate 1');
        $this->vippsCustomerAddressRepository->method('getById')->willReturn($vippsAddress);

        $this->customerSession->expects($this->once())->method('setAddressFormData');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('customer/address/new', ['vipps_address_id' => '42']);

        $this->action->execute();
    }
}

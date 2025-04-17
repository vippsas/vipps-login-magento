<?php

/**
 * Copyright 2020 Vipps
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON
 * INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Vipps\Login\Test\Integration\Model\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Vipps\Login\Model\Block\ClassPool;

class ClassPoolTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ClassPool
     */
    private $classPool;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->classPool = $this->objectManager->create(ClassPool::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store vipps/login/version mobile_epayment
     */
    public function testGetShouldReturnExpectedValueWithMobileVersion()
    {
        $expectedResult = 'brand-mobile';
        $result = $this->classPool->get();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store vipps/login/version vipps_payment
     */
    public function testGetShouldReturnExpectedValueWithVipsVersion()
    {
        $expectedResult = 'brand-vipps';
        $result = $this->classPool->get();
        $this->assertEquals($expectedResult, $result);
    }
}

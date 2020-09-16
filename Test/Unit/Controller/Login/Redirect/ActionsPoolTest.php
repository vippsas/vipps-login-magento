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
namespace Vipps\Login\Test\Unit\Controller\Login\Redirect;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Vipps\Login\Controller\Login\Redirect\Action\ActionInterface;
use Vipps\Login\Controller\Login\Redirect\ActionsPool;
use PHPUnit\Framework\TestCase;

/**
 * Class ActionsPoolTest
 * @package Vipps\Login\Test\Unit\Controller\Login\Redirect
 */
class ActionsPoolTest extends TestCase
{
    public function testGet()
    {
        $action1 = $this->getMockBuilder(ActionInterface::class)
            ->setMethods(['execute'])
            ->getMockForAbstractClass();

        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator'])
            ->getMock();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with([
                    'array' => ['action1' => ActionInterface::class],
                    'type' => ActionInterface::class
                ])
            ->willReturn($tMap);


        $arrayIterator = new \ArrayIterator([$action1]);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn($arrayIterator);

        $token = ['some_array'];
        $action1->expects(static::once())
            ->method('execute')
            ->with($token)
            ->willReturn(true);

        $pool = new ActionsPool(
            $tMapFactory,
            [
                'action1' => ActionInterface::class
            ]
        );

        self::assertTrue($pool->execute($token));
    }

    public function testGetException()
    {
        $action1 = $this->getMockBuilder(ActionInterface::class)
            ->setMethods(['execute'])
            ->getMockForAbstractClass();

        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator'])
            ->getMock();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'action1' => ActionInterface::class
                    ],
                    'type' => ActionInterface::class
                ]
            )
            ->willReturn($tMap);

        $arrayIterator = new \ArrayIterator([$action1]);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn($arrayIterator);

        $token = ['some_array'];
        $action1->expects(static::once())
            ->method('execute')
            ->with($token)
            ->willReturn(false);

        $pool = new ActionsPool(
            $tMapFactory,
            [
                'action1' => ActionInterface::class
            ]
        );

        self::assertFalse($pool->execute($token));
    }
}

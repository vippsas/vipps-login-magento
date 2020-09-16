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

namespace Vipps\Login\Controller\Login\Redirect;

use Magento\Framework\ObjectManager\TMapFactory;
use Vipps\Login\Controller\Login\Redirect\Action\ActionInterface;

/**
 * Class ActionsPool
 * @package Vipps\Login\Controller\Login\Redirect
 */
class ActionsPool
{
    /**
     * @var array
     */
    private $actions;

    /**
     * ActionsPool constructor.
     *
     * @param TMapFactory $tmapFactory
     * @param array $actions
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $actions = []
    ) {
        $this->actions = $tmapFactory->create(
            [
                'array' => $actions,
                'type' => ActionInterface::class
            ]
        );
    }

    /**
     * @param array $token
     *
     * @return bool
     */
    public function execute($token)
    {
        foreach ($this->actions as $action) {
            $result = $action->execute($token);
            if ($result) {
                return $result;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace Vipps\Login\Model\Block;

use Vipps\Login\Model\Config\Version\Pool;
use Vipps\Login\Api\Block\ClassPoolInterface;

class ClassPool implements ClassPoolInterface
{
    private Pool $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function get(): string
    {
        return $this->pool->get();
    }
}

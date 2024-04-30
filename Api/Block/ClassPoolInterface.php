<?php

declare(strict_types=1);

namespace Vipps\Login\Api\Block;

interface ClassPoolInterface
{
    /**
     * Receive class name from module configuration
     *
     * @return string
     */
    public function get(): string;
}

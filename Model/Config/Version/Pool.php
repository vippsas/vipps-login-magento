<?php

declare(strict_types=1);

namespace Vipps\Login\Model\Config\Version;

use Vipps\Login\Model\Config\Config;
use Vipps\Login\Model\Config\Source\Version;

class Pool
{
    private array $pool;
    private Config $config;

    public function __construct(
        Config $config,
        array $pool = [],
    ) {
        $this->pool = $pool;
        $this->config = $config;
    }

    public function get()
    {
        $versionCode = $this->config->getVersion();

        return $this->pool[$versionCode] ?? $this->pool[Version::CONFIG_VIPPS];
    }
}

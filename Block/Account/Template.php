<?php

declare(strict_types=1);

namespace Vipps\Login\Block\Account;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template as MagentoTemplate;
use Magento\Framework\View\Element\Template\Context;
use Vipps\Login\Api\Block\ClassPoolInterface;

class Template extends MagentoTemplate
{
    private ClassPoolInterface $classPool;

    public function __construct(
        ClassPoolInterface $classPool,
        Resolver                  $resolver,
        Context                   $context,
        array                     $data = []
    ) {
        $this->classPool = $classPool;
        $this->resolver = $resolver;

        parent::__construct($context, $data);
    }

    public function getClassName(): string
    {
        return $this->classPool->get();
    }
}

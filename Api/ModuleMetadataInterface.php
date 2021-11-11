<?php
/**
 * Copyright 2021 Vipps
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

namespace Vipps\Login\Api;

/**
 * Interface MetadataInterface.
 *
 * @package Vipps\Login\Api
 */
interface ModuleMetadataInterface
{
    /**
     * The name of the module for the optional Vipps HTTP headers.
     *
     * @var string
     */
    const MODULE_NAME = 'vipps-magento-login';

    /**
     * Get system name, magento in out case.
     *
     * @return string
     */
    public function getSystemName(): string;

    /**
     * Get the system version (eg. 2.3.0, 2.2.1).
     *
     * @return string
     */
    public function getSystemVersion(): string;

    /**
     * Get the name of the current module (`vipps-magento-login`).
     *
     * @return string
     */
    public function getModuleName(): string;

    /**
     * Get the version of the current module (`x.x.x`).
     *
     * @return string
     */
    public function getModuleVersion(): string;
}
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

namespace Vipps\Login\Model;

/**
 * Normalises a Vipps street address into the number of street lines Magento is configured to use
 * (`customer/address/street_lines`). When the address has more physical lines than Magento allows,
 * the overflow is folded into the last line so no street field ever contains a raw line break.
 */
class AddressStreetFormatter
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Split a (possibly multi-line) street address into at most the configured number of lines.
     *
     * @param string $streetAddress
     * @return string[]
     */
    public function format(string $streetAddress): array
    {
        // Clamp to at least one line: the config can be empty/0, which would otherwise drop the address.
        $maxLines = max(1, $this->config->getCustomerStreetLinesNumber());

        // Split on any line-ending style (Vipps is an external source, so don't assume PHP_EOL).
        $lines = preg_split('/\r\n|\r|\n/', trim($streetAddress));
        $lines = array_map('trim', $lines === false ? [''] : $lines);

        if (count($lines) > $maxLines) {
            // Fold the extra physical lines into the last allowed line, joined by spaces.
            $overflow = array_splice($lines, $maxLines - 1);
            $lines[$maxLines - 1] = implode(' ', array_filter($overflow));
        }

        return $lines;
    }
}

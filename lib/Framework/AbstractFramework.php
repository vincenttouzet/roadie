<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Framework;

use Roadie\Framework\Extension\FrameworkExtensionInterface;

abstract class AbstractFramework implements FrameworkInterface
{
    /** @var array */
    private $extensions = [];

    /** @var null */
    private $sorted = null;

    public function addExtension(FrameworkExtensionInterface $extension, $priority = 0)
    {
        $this->extensions[$priority][] = $extension;
        $this->sorted = null;
    }

    /**
     * Sorts the internal list of extensions by priority.
     *
     * @return FrameworkExtensionInterface[]
     */
    public function getExtensions()
    {
        if (null === $this->sorted) {
            ksort($this->extensions);
            $this->sorted = [];

            if (!empty($this->extensions)) {
                $this->sorted = call_user_func_array('array_merge', $this->extensions);
            }
        }

        return $this->sorted;
    }
}

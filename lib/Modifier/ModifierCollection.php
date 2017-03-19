<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Modifier;

class ModifierCollection
{
    /** @var ModifierInterface[] */
    private $modifiers = [];

    public function addModifier(ModifierInterface $modifier)
    {
        $this->modifiers[] = $modifier;
    }

    /**
     * @return ModifierInterface[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }
}

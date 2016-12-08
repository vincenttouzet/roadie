<?php

/*
 * This file is part of the symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Framework;

use Roadie\Modifier\ModifierCollection;

interface FrameworkInterface
{
    /**
     * @param array $config
     * @param ModifierCollection $modifiers
     */
    public function processConfig(array $config, ModifierCollection $modifiers);

    /**
     * @return string
     */
    public function getName();
}

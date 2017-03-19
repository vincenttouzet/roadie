<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Framework\Extension;

use Roadie\Modifier\ModifierCollection;

interface FrameworkExtensionInterface
{
    /**
     * @param array              $config
     * @param ModifierCollection $modifierCollection
     */
    public function processConfig(array $config, ModifierCollection $modifierCollection);
}

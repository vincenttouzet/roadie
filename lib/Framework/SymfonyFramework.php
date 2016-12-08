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

use Roadie\Framework\Extension\SymfonyCoreExtension;
use Roadie\Modifier\ModifierCollection;

class SymfonyFramework extends AbstractFramework
{
    public function __construct()
    {
        $this->addExtension(new SymfonyCoreExtension(), -20);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'symfony';
    }

    /**
     * @param array $config
     * @param ModifierCollection $modifiers
     */
    public function processConfig(array $config, ModifierCollection $modifiers)
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->processConfig($config, $modifiers);
        }
    }
}

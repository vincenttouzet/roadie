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
use Roadie\Modifier\Symfony\AppKernelModifier;
use Roadie\Modifier\YAMLModifier;

class SymfonyCoreExtension implements FrameworkExtensionInterface
{
    /**
     * @param array              $config
     * @param ModifierCollection $modifierCollection
     *
     * @return mixed
     */
    public function processConfig(array $config, ModifierCollection $modifierCollection)
    {
        // bundles
        if (isset($config['bundles']) || isset($config['bundles-dev'])) {
            $bundles = [
                'all' => isset($config['bundles']) ? $config['bundles'] : [],
                'dev' => isset($config['bundles-dev']) ? $config['bundles-dev'] : [],
            ];
            $modifier = new AppKernelModifier('app/AppKernel.php', $bundles);
            $modifierCollection->addModifier($modifier);
        }
        // config
        if (isset($config['config'])) {
            $modifier = new YAMLModifier('app/config/config.yml', $config['config']);
            $modifierCollection->addModifier($modifier);
        }
        if (isset($config['config_dev'])) {
            $modifier = new YAMLModifier('app/config/config_dev.yml', $config['config_dev']);
            $modifierCollection->addModifier($modifier);
        }
        if (isset($config['config_prod'])) {
            $modifier = new YAMLModifier('app/config/config_prod.yml', $config['config_prod']);
            $modifierCollection->addModifier($modifier);
        }
        // Routing
        if (isset($config['routing'])) {
            $modifier = new YAMLModifier('app/config/routing.yml', $config['routing']);
            $modifierCollection->addModifier($modifier);
        }
        if (isset($config['routing_dev'])) {
            $modifier = new YAMLModifier('app/config/routing_dev.yml', $config['routing_dev']);
            $modifierCollection->addModifier($modifier);
        }
        if (isset($config['routing_prod'])) {
            $modifier = new YAMLModifier('app/config/routing_prod.yml', $config['routing_prod']);
            $modifierCollection->addModifier($modifier);
        }
    }
}

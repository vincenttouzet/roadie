<?php

/*
 * This file is part of the symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Config;

use Roadie\Package\Package;
use Symfony\Component\Finder\Finder;

class ConfigLoader
{
    /** @var Package[] */
    private $packages = [];

    /** @var Finder */
    private $finder;

    public function __construct(Finder $finder = null)
    {
        if (is_null($finder)) {
            $finder = new Finder();
            $finder->in('.')
                ->name('roadie*.json');
        }

        $this->finder = $finder;
    }

    public function load()
    {
        // todo add cache
        foreach ($this->finder->files() as $file) {
            $config = json_decode(file_get_contents($file->getPathname()), true);
            $package = new Package();
            $package->setName($config['name']);
            foreach ($config['frameworks'] as $framework => $fwConfig) {
                $package->addFrameworkConfig($framework, $fwConfig);
            }

            $this->packages[] = $package;
        }

        return $this->packages;
    }

    /**
     * @return \Roadie\Package\Package[]
     */
    public function getPackages()
    {
        return $this->packages;
    }
}

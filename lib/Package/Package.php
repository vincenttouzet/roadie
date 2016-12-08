<?php

/*
 * This file is part of the symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Package;

class Package
{
    /** @var string */
    private $name;

    /** @var array */
    private $frameworksConfig = [];

    public function addFrameworkConfig($framework, $config)
    {
        $this->frameworksConfig[$framework] = $config;
    }

    public function getFrameworkConfig($framework)
    {
        if (isset($this->frameworksConfig[$framework])) {
            return $this->frameworksConfig[$framework];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

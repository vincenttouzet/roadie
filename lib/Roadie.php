<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie;

use Composer\Composer;
use Composer\IO\IOInterface;
use Roadie\Config\ConfigLoader;
use Roadie\Framework\FrameworkInterface;
use Roadie\Modifier\ModifierCollection;

class Roadie
{
    /** @var Composer */
    protected $composer;

    /** @var array */
    protected $composerPackages = null;

    /** @var ConfigLoader */
    protected $configLoader;

    /** @var FrameworkInterface */
    protected $framework;

    /** @var IOInterface */
    protected $io;

    public function __construct(Composer $composer, ConfigLoader $loader)
    {
        $this->composer = $composer;
        $this->getComposerPackages();
        $this->configLoader = $loader;
        $loader->load();
    }

    public function configure()
    {
        if (!$this->getFramework()) {
            throw new \InvalidArgumentException('You must define a framework to handle configurations');
        }
        $modifiers = new ModifierCollection();
        foreach ($this->configLoader->getPackages() as $package) {
            if (in_array($package->getName(), $this->getComposerPackages())) {
                $config = $package->getFrameworkConfig($this->getFramework()->getName());
                $this->getFramework()->processConfig($config, $modifiers);
            }
        }
        // show diffs
        foreach ($modifiers->getModifiers() as $modifier) {
            if ($this->getIo()) {
                $diff = $modifier->diff();
                if ($diff->hasDiff()) {
                    // show diff to user
                    $this->getIo()->write(sprintf('<info>%s</info>', $modifier->getName()));
                    $this->getIo()->write($diff->asString());
                    // ask for update
                    $modify = $this->getIo()->askConfirmation('Do you want to apply these updates ? [y/N]');
                    if ($modify) {
                        $modifier->modify();
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getComposerPackages()
    {
        if (!$this->composerPackages) {
            $this->composerPackages = [];
            foreach ($this->composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
                $this->composerPackages[] = $package->getName();
            }
        }

        return $this->composerPackages;
    }

    /**
     * @return FrameworkInterface
     */
    public function getFramework()
    {
        return $this->framework;
    }

    /**
     * @param FrameworkInterface $framework
     */
    public function setFramework($framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return IOInterface
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIo($io)
    {
        $this->io = $io;
    }
}

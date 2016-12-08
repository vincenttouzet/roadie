<?php

/*
 * This file is part of the symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Composer;


use Composer\Script\Event;
use Roadie\Config\ConfigLoader;
use Roadie\Diff\Formatter\IOFormatter;
use Roadie\Framework\SymfonyFramework;
use Roadie\Roadie;
use Symfony\Component\Finder\Finder;

class ScriptHandler
{
    public static function configure(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();
        $io = $event->getIO();

        if (!isset($extras['roadie'])) {
            throw new \InvalidArgumentException('Roadie needs to be configured through the extra.roadie setting.');
        }

        $framework = $extras['roadie']['framework'];
        if (!class_exists($framework)) {
            $framework = sprintf('Roadie\\Framework\\%sFramework', ucfirst($framework));
        }
        $framework = new $framework();


        // init environment
        $configLoader = new ConfigLoader();
        $roadie = new Roadie($event->getComposer(), $configLoader);
        $roadie->setFramework($framework);
        $roadie->setIo($io);
        $roadie->configure();
    }
}

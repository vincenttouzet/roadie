<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Modifier\Symfony;

use Roadie\Modifier\AbstractTextFileModifier;

class AppKernelModifier extends AbstractTextFileModifier
{
    /** @var array */
    private $newLines = [];

    /** @var array List of current bundles */
    private $bundles = [];

    private $allIndent = null;
    private $devIndent = null;

    /**
     * @return string
     */
    public function getName()
    {
        return 'File app/AppKernel.php';
    }

    protected function process()
    {
        $data = $this->getData();
        // new bundles to add
        $newBundles = isset($data['all']) ? $data['all'] : [];
        $newDevBundles = isset($data['dev']) ? $data['dev'] : [];

        if (!is_array($newBundles)) {
            $newBundles = [$newBundles];
        }
        if (!is_array($newDevBundles)) {
            $newDevBundles = [$newDevBundles];
        }

        // if no all and dev keys : data are bundles
        if (!count($newBundles) && !count($newDevBundles)) {
            $newBundles = $data;
            if (!is_array($newBundles)) {
                $newBundles = [$newBundles];
            }
        }

        $lines = preg_split("/(\r\n|\r|\n)/", $this->original);

        // flags
        $isInBundleArray = false;
        $isInDevBundles = false;
        foreach ($lines as $lineNumber => $line) {
            $this->newLines[] = $line;

            // all bundles
            if ($isInBundleArray && preg_match('/^(]|\();$/', trim($line))) {
                $isInBundleArray = false;
                array_pop($this->newLines);
                // add new bundles if not already in declared bundles
                $this->addBundles($newBundles, 'all');
                $this->newLines[] = $line;
            }
            if ($isInBundleArray) {
                // add to current bundles array
                if (preg_match('/^(?P<indent>\s*)?new\s*(?P<bundle>.*)\(.*\),?$/', $line, $matches)) {
                    $namespaceParts = explode('\\', $matches['bundle']);
                    $bundleName = array_pop($namespaceParts);
                    $this->bundles[$bundleName] = $matches['bundle'];
                    $this->allIndent = $matches['indent'];
                }
            }
            if (preg_match('/^\s*\$bundles\s*=\s*(\[|array\()$/', $line)) {
                $isInBundleArray = true;
            }

            // dev bundles
            if ($isInDevBundles && '}' === trim($line)) {
                $isInDevBundles = false;
                array_pop($this->newLines);
                // add new bundles if not already in declared bundles
                $this->addBundles($newDevBundles, 'dev');
                $this->newLines[] = $line;
            }
            if ($isInDevBundles) {
                // add to current bundles array
                if (preg_match('/^(?P<indent>\s*)?\$bundles\[\]\s*=\s*new\s*(?P<bundle>.*)\(.*\);$/', $line, $matches)) {
                    $namespaceParts = explode('\\', $matches['bundle']);
                    $bundleName = array_pop($namespaceParts);
                    $this->bundles[$bundleName] = $matches['bundle'];
                    $this->devIndent = $matches['indent'];
                }
            }
            if (strstr($line, '$this->getEnvironment()') !== false && strstr($line, 'dev') !== false) {
                $isInDevBundles = true;
            }
        }

        $this->new = implode(PHP_EOL, $this->newLines);
    }

    private function addBundles($bundles, $part)
    {
        foreach ($bundles as $bundle) {
            // check if not already in declared bundles
            preg_match('/^(?P<bundle>[A-Za-z0-9\\\\]*)?(?P<has_arguments>\(.*\))?$/', $bundle, $matches);
            $bundle = isset($matches['bundle']) ? $matches['bundle'] : null;
            if ($bundle && !in_array($bundle, $this->bundles)) {
                // add new line
                $has_arguments = isset($matches['has_arguments']) ? $matches['has_arguments'] : null;
                switch ($part) {
                    case 'all':
                        $line = sprintf(
                            '%snew %s%s,',
                            $this->allIndent,
                            $bundle,
                            $has_arguments ? trim($has_arguments) : '()'
                        );
                        $this->newLines[] = $line;
                        break;
                    case 'dev':
                        $line = sprintf(
                            '%s$bundles[] = new %s%s;',
                            $this->devIndent,
                            $bundle,
                            $has_arguments ? trim($has_arguments) : '()'
                        );
                        $this->newLines[] = $line;
                        break;
                }
            }
        }
    }
}

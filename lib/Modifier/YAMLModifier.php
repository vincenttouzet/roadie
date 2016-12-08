<?php

/*
 * This file is part of the symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Modifier;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Yaml;

class YAMLModifier extends AbstractTextFileModifier
{
    const PATTERN_LINE = '/^(?P<indent>\s*)?(?P<tag>[\w\._]*[:\-])?(?P<spaces>\s*)?(?P<value>.*)?$/';

    private $newLines = [];
    private $currentIndentation = '';
    private $previousIndentation = '';
    private $fileIndentation = null;
    private $currentBlockIndentation = '';
    private $currentPath = [];
    private $currentBlock = '';
    private $currentBlockIsItemCollection = false;
    private $itemCollection = [];
    private $previousValuePath = [];
    /** @var  PropertyAccessor */
    private $propertyAccessor;
    private $dataToWrite;

    protected function process()
    {
        $this->new = $this->original;

        $this->dataToWrite = $this->data;

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        $lines = preg_split("/(\r\n|\r|\n)/", $this->original);

        foreach ($lines as $index => $line) {
            $this->newLines[] = $line;
            // empty line ?
            if ($this->isLineEmpty($line)) {
                continue;
            }
            // get line parts
            preg_match(self::PATTERN_LINE, $line, $lineMatches);
            if (!isset($lineMatches['indent'])) {
                $lineMatches['indent'] = '';
            }
            if (!isset($lineMatches['tag'])) {
                $lineMatches['tag'] = '';
            }
            if (!isset($lineMatches['spaces'])) {
                $lineMatches['spaces'] = '';
            }
            if (!isset($lineMatches['value'])) {
                $lineMatches['value'] = '';
            }
            $this->previousIndentation = $this->currentIndentation;
            $this->currentIndentation = $lineMatches['indent'];
            if (is_null($this->fileIndentation) && strlen($this->currentIndentation) > strlen($this->previousIndentation)) {
                $this->fileIndentation = strlen($this->currentIndentation) - strlen($this->previousIndentation);
            }
            $this->currentBlock = trim($lineMatches['tag'], ':');
            // new block ?
            if (!$lineMatches['indent'] && $lineMatches['tag'] && !$lineMatches['value']) {
                // new block
                // check if on itemCollection and add if needed
                if ($this->currentBlockIsItemCollection) {
                    $this->addToCollection();
                }
                // check and add sub block if needed
                if (count($this->currentPath) > 1) {
                    $path = $this->currentPath;
                    array_pop($path);
                    $this->addSubBlock($path);
                }
                $this->currentPath = [];
                $this->currentPath[] = $this->currentBlock;
                $this->currentBlockIndentation = $lineMatches['indent'];
                $this->currentBlockIsItemCollection = false;
                $this->itemCollection = [];
            } elseif ($lineMatches['tag'] && !$lineMatches['value']) {
                // new sub block
                if ($this->currentBlockIsItemCollection) {
                    $this->addToCollection();
                }
                if (strlen($this->currentIndentation) < strlen($this->currentBlockIndentation)) {
                    // indentation < current block : value of parent parent block
                    for ($i = 0; $i <= strlen($this->currentBlockIndentation) - strlen($this->currentIndentation); $i += $this->fileIndentation) {
                        $this->addSubBlock($this->currentPath);
                        array_pop($this->currentPath);
                    }
                } elseif (strlen($this->currentIndentation) == strlen($this->currentBlockIndentation)) {
                    // same indentation as current block : value of parent block
                    // check if on itemCollection and add if needed
                    array_pop($this->currentPath);
                }
                $this->currentPath[] = $this->currentBlock;
                $this->currentBlockIndentation = $lineMatches['indent'];
                $this->currentBlockIsItemCollection = false;
                $this->itemCollection = [];
            } else {
                if (!$this->currentBlock) {
                    // multi-line value
                    // if previous path has been replaced ...
                    if (count($this->previousValuePath)) {
                        $replaced = $this->propertyAccessor->getValue($this->data, '['.implode('][', $this->previousValuePath).']');
                        if ($replaced) {
                            // ... remove current line
                            array_pop($this->newLines);
                        }
                    }
                } elseif ($this->currentBlock === '-') {
                    // collection item
                    $this->currentBlockIsItemCollection = true;
                    $this->itemCollection[] = $lineMatches['value'];
                } else {
                    $valuePath = $this->currentPath;
                    $valuePath[] = $this->currentBlock;
                    $this->previousValuePath = $valuePath;
                    $replace = $this->propertyAccessor->getValue($this->dataToWrite, '['.implode('][', $valuePath).']');
                    if (!is_null($replace)) {
                        // replace value
                        array_pop($this->newLines);
                        if (!is_string($replace)) {
                            $replace = Yaml::dump($replace);
                        }
                        // todo escape string value and keep comment ?
                        $this->newLines[] = $this->currentIndentation.$this->currentBlock.':'.$lineMatches['spaces'].$replace;
                        // remove from data
                        $this->dataToWrite = $this->removeData($this->dataToWrite, $valuePath);
                    }
                }
            }
        }
        if ($this->currentBlockIsItemCollection) {
            $this->addToCollection();
        }
        // check and add sub block if needed
        while (count($this->currentPath)) {
            $this->addSubBlock($this->currentPath);
            array_pop($this->currentPath);
        }
        // check if need to add new blocks
        if (count($this->dataToWrite)) {
            $newBlocks = $this->dumpYAML($this->dataToWrite);
            $newBlocksLines = explode(PHP_EOL, $newBlocks);
            foreach ($newBlocksLines as $line) {
                $this->newLines[] = $line;
            }
        }

        $this->new = implode(PHP_EOL, $this->newLines);
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'File '.$this->getFile();
    }

    private function addToCollection()
    {
        $valuePath = $this->currentPath;
        $replace = $this->propertyAccessor->getValue($this->dataToWrite, '['.implode('][', $valuePath).']');
        if ($replace && is_array($replace)) {
            // remove added line
            $lastLine = array_pop($this->newLines);
            // pop empty lines
            $popedLines =  $this->popEmptyLines();
            if (count($replace) && $replace[0] === 'ITEM_COLLECTION_OVERRIDE') {
                // remove special entry
                array_shift($replace);
                // remove items
                while (preg_match('/^\s*\-/', $this->newLines[count($this->newLines) - 1])) {
                    array_pop($this->newLines);
                }
            }
            // add values
            foreach ($replace as $replaceValue) {
                // check if value not already added
                if (!in_array($replaceValue, $this->itemCollection)) {
                    // todo escape string if not already escaped
                    // todo use Yaml::dump ?
                    if ($this->isLineEmpty($lastLine)) {
                        $this->newLines[] = $this->currentIndentation.'- '.$replaceValue;
                    } else {
                        $this->newLines[] = $this->previousIndentation.'- '.$replaceValue;
                    }
                }
            }
            // re-add empty lines
            foreach ($popedLines as $popedLine) {
                $this->newLines[] = $popedLine;
            }
            // re-add last line
            $this->newLines[] = $lastLine;
            $this->dataToWrite = $this->removeData($this->dataToWrite, $valuePath);
        }
    }

    private function addSubBlock($path)
    {
        if (count($path)) {
            $newData = $this->propertyAccessor->getValue($this->dataToWrite, '['.implode('][', $path).']');
            if ($newData) {
                // must insert
                // remove added line
                $lastLine = array_pop($this->newLines);
                // pop empty lines
                $popedLines =  $this->popEmptyLines();
                // dump new blocks
                $data = $this->dumpYAML($newData, str_repeat(' ', count($path) * $this->fileIndentation));
                $lines = explode(PHP_EOL, $data);
                foreach ($lines as $line) {
                    // add indentation
                    $this->newLines[] = $line;
                }
                // re-add empty lines
                foreach ($popedLines as $popedLine) {
                    $this->newLines[] = $popedLine;
                }
                // re-add last line
                $this->newLines[] = $lastLine;
                $this->dataToWrite = $this->removeData($this->dataToWrite, $path);
            }
        }
    }

    private function popEmptyLines()
    {
        $popedLines = [];
        while ($this->isLineEmpty($this->newLines[count($this->newLines) - 1])) {
            $popedLines[] = array_pop($this->newLines);
        }

        return $popedLines;
    }

    private function isLineEmpty($line)
    {
        return strlen(trim($line)) === 0 || substr(trim($line), 0, 1) === '#';
    }

    /**
     * Simple dump function that keep comments
     *
     * @param $data
     * @param string $indent
     *
     * @return string
     */
    private function dumpYAML($data, $indent = '')
    {
        $output = '';
        foreach ($data as $tag => $value) {
            if (is_array($value)) {
                $output .= $indent.$tag.':'.PHP_EOL;
                $output .= $this->dumpYAML($value, $indent.'    ');
            } elseif (is_int($tag)) {
                // comment
                if (!$value['0'] === '#') {
                    $value = '#'.$value;
                }
                $output .= $indent.$value.PHP_EOL;
            } elseif (is_numeric($value)) {
                // numeric value
                $output .= $indent.$tag.': '.$value.PHP_EOL;
            } else {
                // scalar
                $comment = '';
                if (preg_match('/^(?P<value>[^#]+)(?P<comment>\s*#.*)$/', $value, $matches)) {
                    $value = $matches['value'];
                    $comment = $matches['comment'];
                } else {

                }
                $value = trim($value);
                $comment = trim($comment);
                // escape value ?
                if (!is_numeric($value) && $value[0] !== '\'' && $value[strlen($value) - 1] !== '\'') {
                     $value = '\''.str_replace('\'', '\'\'', $value).'\'';
                }
                $output .= $indent.$tag.': '.$value.' '.$comment.PHP_EOL;
            }
        }

        return $output;
    }

    private function removeData($data, $path)
    {
        $key = array_shift($path);

        if (isset($data[$key])) {
            if (count($path) > 0) {
                $data[$key] = $this->removeData($data[$key], $path);
                // check if empty array or just comments lines
                $data[$key] = array_filter($data[$key], function ($value) {
                    if (is_string($value) && '#' === substr(trim($value), 0, 1)) {
                        return false;
                    }

                    return true;
                });
                if (0 === count($data[$key])) {
                    unset($data[$key]);
                }
            } else {
                unset($data[$key]);
            }
        }

        return $data;
    }
}

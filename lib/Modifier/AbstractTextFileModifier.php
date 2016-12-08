<?php

/*
 * This file is part of the Roadie - Symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Modifier;

use Roadie\Diff\Diff;
use Roadie\Modifier\Exception\AlreadyModifiedException;
use SebastianBergmann\Diff\Differ;

abstract class AbstractTextFileModifier implements ModifierInterface
{
    /** @var string Path of the file */
    protected $file;

    /** @var mixed Data to update/set on file */
    protected $data;

    /** @var bool Flag to indicate if file is modified or not */
    protected $modified = false;

    /** @var bool Flag to indicate if the process is done */
    protected $processed = false;

    /** @var string Original file content */
    protected $original = '';

    /** @var string New file content */
    protected $new = '';

    /**
     * YamlModifier constructor.
     * @param string $file File to update
     * @param mixed $data Data to update on file
     */
    public function __construct($file, $data)
    {
        $this->setFile($file);
        $this->setData($data);
    }

    abstract protected function process();

    /**
     * @return mixed
     */
    public function diff()
    {
        if (!$this->processed) {
            $this->process();
            $this->processed = true;
        }

        return new Diff($this->original, $this->new);
    }

    /**
     * @return mixed
     */
    public function modify()
    {
        if (!$this->processed) {
            $this->process();
        }
        $this->modified = true;
        file_put_contents($this->file, $this->new);
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @throws AlreadyModifiedException
     */
    public function setFile($file)
    {
        if ($this->modified) {
            throw new AlreadyModifiedException($this);
        }
        $this->file = $file;
        $this->original = file_get_contents($this->file);
        $this->processed = false;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @throws AlreadyModifiedException
     */
    public function setData($data)
    {
        if ($this->modified) {
            throw new AlreadyModifiedException($this);
        }
        $this->data = $data;
        $this->processed = false;
    }
}

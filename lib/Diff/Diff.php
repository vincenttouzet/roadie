<?php

/*
 * This file is part of the Roadie - Symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Diff;

use SebastianBergmann\Diff\Differ;

class Diff
{
    /** @var string Original content */
    private $from;

    /** @var string New content */
    private $to;

    /** @var Differ */
    private $differ;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
        $this->differ = new Differ();
    }

    public function hasDiff()
    {
        return $this->from !== $this->to;
    }

    /**
     * Return diff as a string
     *
     * @return string
     */
    public function asString()
    {
        return $this->differ->diff($this->from, $this->to);
    }

    /**
     * Return diff as array
     *
     * @return array
     */
    public function asArray()
    {
        return $this->differ->diffToArray($this->from, $this->to);
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }
}

<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Modifier;

use Roadie\Diff\Diff;

interface ModifierInterface
{
    /**
     * Get the diff if modifier is applied.
     *
     * @return Diff
     */
    public function diff();

    /**
     * Actually apply modification.
     *
     * @return mixed
     */
    public function modify();

    /**
     * @return string
     */
    public function getName();
}

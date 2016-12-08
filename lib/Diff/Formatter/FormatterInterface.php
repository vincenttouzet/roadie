<?php

/*
 * This file is part of the Roadie - Symfony project.
 *
 * (c) Vincent Touzet <vincent.touzet@dotsafe.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Diff\Formatter;

use Roadie\Diff\Diff;

interface FormatterInterface
{
    /**
     * Format the given diff
     *
     * @param Diff $diff
     *
     * @return mixed
     */
    public function format(Diff $diff);
}

<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Diff\Formatter;

use Roadie\Diff\Diff;

class IOFormatter implements FormatterInterface
{
    /**
     * Format the given diff.
     *
     * @param Diff $diff
     *
     * @return mixed
     */
    public function format(Diff $diff)
    {
        $string = $diff->asString();

        $lines = explode(PHP_EOL, $string);
        foreach ($lines as &$line) {
            if (strlen($line)) {
                if ($line[0] === '+') {
                    $line = sprintf('<error>%s</error>', $line);
                } elseif ($line[0] === '-') {
                    //$line = sprintf('<error>%s</error>', $line);
                }
            }
        }

        return implode(PHP_EOL, $lines);
    }
}

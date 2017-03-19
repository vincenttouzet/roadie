<?php

/*
 * This file is part of the Roadie package.
 *
 * (c) Vincent Touzet <vincent.touzet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Roadie\Modifier\Exception;

use Roadie\Modifier\ModifierInterface;

class AlreadyModifiedException extends \Exception
{
    /** @var ModifierInterface */
    protected $modifier;

    public function __construct(ModifierInterface $modifier)
    {
        $this->modifier = $modifier;
        parent::__construct('You can not update the modifier as it has already modified.');
    }

    /**
     * @return ModifierInterface
     */
    public function getModifier()
    {
        return $this->modifier;
    }
}

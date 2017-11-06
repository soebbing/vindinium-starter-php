<?php

namespace Vindinium;

use Vindinium\Structs\Position;

interface PositionableInterface
{
    /**
     * @return Position
     */
    public function getPosition();
}

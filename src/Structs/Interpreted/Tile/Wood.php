<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Position;

class Wood extends Tile
{
    /**
     * @param Position $position
     */
    public function __construct(Position $position)
    {
        $this->walkable = false;
        parent::__construct($position);
    }
}
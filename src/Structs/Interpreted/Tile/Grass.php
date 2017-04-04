<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Position;
use Vindinium\Structs\Interpreted\Tile;

class Grass extends Tile
{
    /**
     * @param Position $position
     */
    public function __construct(Position $position)
    {
        $this->walkable = true;
        parent::__construct($position);
    }
}
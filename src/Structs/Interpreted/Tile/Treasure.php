<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Hero;
use Vindinium\Structs\Position;
use Vindinium\Structs\Interpreted\Tile;

class Treasure extends Tile
{
    /** @var \Vindinium\Structs\Hero|null */
    private $owner;

    /**
     * @param Position $position
     * @param Hero $owner
     */
    public function __construct(Position $position, Hero $owner = null)
    {
        $this->walkable = false;
        $this->owner = $owner;
        parent::__construct($position);
    }

    /**
     * @return null|\Vindinium\Structs\Hero
     */
    public function getOwner()
    {
        return $this->owner;
    }
}

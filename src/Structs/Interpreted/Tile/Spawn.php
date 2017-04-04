<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Position;

class Spawn extends Tile
{
    /** @var \Vindinium\Structs\Hero */
    private $owner;

    /**
     * @param Position $position
     * @param Hero $owner
     */
    public function __construct(Position $position, Hero $owner)
    {
        $this->walkable = true;
        $this->owner = $owner;
        parent::__construct($position);
    }

    /**
     * @return Hero
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
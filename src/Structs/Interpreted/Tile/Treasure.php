<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Position;
use Vindinium\Structs\Hero as HeroStruct;
use Vindinium\Structs\Interpreted\Tile;

class Treasure extends Tile
{
    /** @var \Vindinium\Structs\Hero|null */
    private $owner;

    /**
     * @param Position $position
     * @param HeroStruct $owner
     */
    public function __construct(Position $position, HeroStruct $owner = null)
    {
        $this->walkable = true;
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
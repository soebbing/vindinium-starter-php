<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Interpreted\Tile;
use Vindinium\Structs\Position;

class Hero extends Tile
{
    /** @var \Vindinium\Structs\Hero */
    private $hero;

    /**
     * @param Position $position
     */
    public function __construct(Position $position)
    {
        $this->walkable = false;

        parent::__construct($position);
    }

    /**
     * @return \Vindinium\Structs\Hero
     */
    public function getHero()
    {
        return $this->hero;
    }
}
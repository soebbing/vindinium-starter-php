<?php

namespace Vindinium\Structs\Interpreted\Tile;

use Vindinium\Structs\Position;
use Vindinium\Structs\Hero as HeroStruct;
use Vindinium\Structs\Interpreted\Tile;

class Hero extends Tile
{
    /** @var \Vindinium\Structs\Hero */
    private $hero;

    /**
     * @param Position $position
     * @param HeroStruct|null $hero
     */
    public function __construct(Position $position, HeroStruct $hero = null)
    {
        $this->walkable = false;
        $this->hero = $hero;

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
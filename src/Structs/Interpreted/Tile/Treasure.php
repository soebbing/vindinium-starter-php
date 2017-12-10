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

    /**
     * @param Hero|null $hero
     * @return bool
     */
    public function isWalkable(Hero $hero = null)
    {
        if (!$hero || !$this->owner) {
            return $this->walkable;
        }

        return $this->owner && $this->owner->getName() !== $hero->getName();
    }
}

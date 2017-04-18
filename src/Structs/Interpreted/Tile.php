<?php

namespace Vindinium\Structs\Interpreted;

use JMGQ\AStar\AbstractNode;
use Vindinium\Structs\Distance;
use Vindinium\Structs\Position;
use Vindinium\PositionableInterface;

abstract class Tile extends AbstractNode implements PositionableInterface
{
    const HERO = 'Hero';
    const GRASS = 'Grass';
    const TAVERN = 'Tavern';
    const TREASURE = 'Treasure';
    const SPAWN = 'Spawn';
    const WOOD = 'Wood';

    /** @var Position */
    protected $position;

    /** @var bool */
    protected $walkable;

    /**
     * @param Position $position
     */
    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    /**
     * @param PositionableInterface $positionable
     * @return Distance
     */
    public function getDistance(PositionableInterface $positionable)
    {
        $sourcePos = $positionable->getPosition();
        $xDistance = 0;
        $YDistance = 0;

        if ($this->position->getX() > $sourcePos->getX()) {
            $xDistance = $this->position->getX() - $sourcePos->getX();
        } else {
            $xDistance = $sourcePos->getX() - $this->position->getX();
        }

        if ($this->position->getY() > $sourcePos->getY()) {
            $yDistance = $this->position->getY() - $sourcePos->getY();
        } else {
            $yDistance = $sourcePos->getY() - $this->position->getY();
        }

        $distance = new Distance(
            $xDistance,
            $yDistance,
            $this
        );

        return $distance;
    }

    /**
     * @return Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function isWalkable()
    {
        return $this->walkable;
    }

    /**
     * @return string
     */
    public function getType()
    {
        $elements = explode('\\', get_class($this));
        return array_pop($elements);
    }

    /**
     * Obtains the node's unique ID
     * @return string
     */
    public function getID()
    {
        return $this->position->getID();
    }

    public function getX()
    {
        return $this->position->getX();
    }

    public function getY()
    {
        return $this->position->getY();
    }
}
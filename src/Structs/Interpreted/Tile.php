<?php

namespace Vindinium\Structs\Interpreted;

use Vindinium\Structs\Distance;
use Vindinium\Structs\Position;
use Vindinium\PositionableInterface;

abstract class Tile implements PositionableInterface
{
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
}
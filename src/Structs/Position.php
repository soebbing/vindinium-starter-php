<?php

namespace Vindinium\Structs;

use JMGQ\AStar\AbstractNode;
use Vindinium\PositionableInterface;

class Position implements PositionableInterface
{
    /** @var int */
    private $x;

    /** @var int */
    private $y;

    /**
     * @param int $x
     * @param int $y
     */
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param array $json
     * @return Position
     */
    public static function fromJson(array $json)
    {
        return new Position($json['x'], $json['y']);
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->getX() . 'x' .  $this->getY();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->getX()}x{$this->getY()}";
    }
}
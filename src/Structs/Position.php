<?php

namespace Vindinium\Structs;

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
    public function __construct($x, $y)
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
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return Position
     */
    public function getPosition()
    {
        return $this;
    }
}
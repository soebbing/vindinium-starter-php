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
     * @param array $json
     * @return Position
     */
    public static function fromJson(array $json)
    {
        $position = new Position();
        foreach ($json as $key => $value) {
            $position->$key = $value;
        }

        return $position;
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
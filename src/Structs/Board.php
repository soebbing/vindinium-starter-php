<?php

namespace Vindinium\Structs;

class Board
{
    const South = 'South';
    const North = 'North';
    const West = 'West';
    const East = 'East';
    const Stay = 'Stay';

    /** @var int */
    private $size;

    /** @var string */
    private $tiles;

    /**
     * @param array $json
     * @return Board
     */
    public static function fromJson(array $json)
    {
        $board = new Board();
        foreach ($json as $key => $value) {
            $board->$key = $value;
        }

        return $board;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getTiles()
    {
        return $this->tiles;
    }
}
<?php

namespace Vindinium\Bot;

use Vindinium\BotInterface;
use Vindinium\Structs\Board;
use Vindinium\Structs\State;

/**
 * VERY simple bot implementation
 */
class Random implements BotInterface
{
    /**
     * @param State $state
     * @return mixed
     */
    public function move(State $state)
    {
        $dirs = [Board::Stay, Board::North, Board::South, Board::West, Board::East];

        return $dirs[array_rand($dirs)];
    }
}